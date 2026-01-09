<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ScheduledItem;
use App\Models\Transaction;
use App\Services\MonthLockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function __construct(private MonthLockService $monthLockService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $monthString = $request->input('month', Carbon::today()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $accounts = Account::where('user_id', $user->id)->orderBy('name')->get();
        $accountId = $accounts->firstWhere('id', (int) $request->input('account_id'))?->id;
        $reconciledFilter = $request->input('reconciled');

        $transactions = Transaction::where('user_id', $user->id)
            ->when($accountId, function ($query) use ($accountId) {
                $query->where(function ($query) use ($accountId) {
                    $query->where('from_account_id', $accountId)
                        ->orWhere('to_account_id', $accountId)
                        ->orWhere('account_id', $accountId);
                });
            })
            ->when($reconciledFilter === 'yes', fn ($query) => $query->where('is_reconciled', true))
            ->when($reconciledFilter === 'no', fn ($query) => $query->where('is_reconciled', false))
            ->whereBetween('date', [$start, $end])
            ->with(['fromAccount', 'toAccount', 'account', 'scheduledItem.recurringRule'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $duplicateHashes = Transaction::where('user_id', $user->id)
            ->whereNotNull('hash')
            ->select('hash')
            ->groupBy('hash')
            ->havingRaw('count(*) > 1')
            ->pluck('hash')
            ->all();

        return view('transactions.index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'month' => $start,
            'selectedAccountId' => $accountId,
            'duplicateHashes' => $duplicateHashes,
            'reconciledFilter' => $reconciledFilter,
        ]);
    }

    public function create(Request $request): View
    {
        $accounts = Account::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('transactions.create', [
            'accounts' => $accounts,
            'today' => Carbon::today(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $accounts = Account::where('user_id', $request->user()->id)->get();
        $accountIds = $accounts->pluck('id')->all();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'in:income,expense,transfer,credit_charge,credit_payment,adjustment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:10'],
            'from_account_id' => ['nullable', Rule::in($accountIds)],
            'to_account_id' => ['nullable', Rule::in($accountIds)],
            'account_id' => ['nullable', Rule::in($accountIds)],
            'memo' => ['nullable', 'string', 'max:255'],
        ]);

        $type = $validated['type'];
        $date = Carbon::parse($validated['date']);
        $accountsById = $accounts->keyBy('id');
        $fundingTypes = ['income', 'cash'];

        if ($this->monthLockService->isLocked($request->user(), $date)) {
            $this->logTransactionIssue($request, 'Month is locked for new entries.', $validated);
            return back()->withInput()->withErrors(['date' => 'This month is locked. Unlock it to add new entries.']);
        }

        if ($type === 'income') {
            if (empty($validated['to_account_id'])) {
                $this->logTransactionIssue($request, 'Income missing destination account.', $validated);
                return back()->withInput()->withErrors(['to_account_id' => 'Select an account for this income.']);
            }
            $validated['from_account_id'] = null;
            $validated['account_id'] = null;
        } elseif ($type === 'expense') {
            if (empty($validated['from_account_id'])) {
                $this->logTransactionIssue($request, 'Expense missing source account.', $validated);
                return back()->withInput()->withErrors(['from_account_id' => 'Select a source account for this expense.']);
            }
            $validated['to_account_id'] = null;
            $validated['account_id'] = null;

            $fromAccount = $accountsById->get((int) $validated['from_account_id']);
            if ($fromAccount?->type === 'credit_card') {
                $validated['type'] = 'credit_charge';
                $validated['account_id'] = $validated['from_account_id'];
                $validated['from_account_id'] = null;
            }
        } elseif ($type === 'transfer') {
            if (empty($validated['from_account_id']) || empty($validated['to_account_id'])) {
                $this->logTransactionIssue($request, 'Transfer missing source or destination account.', $validated);
                return back()->withInput()->withErrors(['to_account_id' => 'Transfers need both source and destination accounts.']);
            }
            if ($validated['from_account_id'] === $validated['to_account_id']) {
                $this->logTransactionIssue($request, 'Transfer source and destination match.', $validated);
                return back()->withInput()->withErrors(['to_account_id' => 'Source and destination cannot match.']);
            }
            $validated['account_id'] = null;
        } elseif ($type === 'credit_charge') {
            if (empty($validated['account_id'])) {
                $this->logTransactionIssue($request, 'Credit charge missing credit card account.', $validated);
                return back()->withInput()->withErrors(['account_id' => 'Choose the credit card for this charge.']);
            }

            $cardAccount = $accountsById->get((int) $validated['account_id']);

            if (! $cardAccount || $cardAccount->type !== 'credit_card') {
                $this->logTransactionIssue($request, 'Credit charge not targeting credit card account.', $validated);
                return back()->withInput()->withErrors(['account_id' => 'Select a credit card account for this charge.']);
            }

            $validated['from_account_id'] = null;
            $validated['to_account_id'] = null;
        } elseif ($type === 'credit_payment') {
            if (empty($validated['from_account_id']) || empty($validated['to_account_id'])) {
                $this->logTransactionIssue($request, 'Credit payment missing funding account or card.', $validated);
                return back()->withInput()->withErrors(['to_account_id' => 'Payments need a funding account and card.']);
            }
            if ($validated['from_account_id'] === $validated['to_account_id']) {
                $this->logTransactionIssue($request, 'Credit payment funding and card match.', $validated);
                return back()->withInput()->withErrors(['to_account_id' => 'Funding account and card must differ.']);
            }

            $fundingAccount = $accountsById->get((int) $validated['from_account_id']);
            $cardAccount = $accountsById->get((int) $validated['to_account_id']);

            if (! $cardAccount || $cardAccount->type !== 'credit_card') {
                $this->logTransactionIssue($request, 'Credit payment destination is not a credit card.', $validated);
                return back()->withInput()->withErrors(['to_account_id' => 'Choose a credit card account to receive the payment.']);
            }

            if (! $fundingAccount || ! in_array($fundingAccount->type, $fundingTypes, true)) {
                $this->logTransactionIssue($request, 'Credit payment funding account not cash or income.', $validated);
                return back()->withInput()->withErrors(['from_account_id' => 'Funding account must be cash or income.']);
            }

            $validated['account_id'] = null;
        } elseif ($type === 'adjustment') {
            if (empty($validated['account_id'])) {
                $this->logTransactionIssue($request, 'Adjustment missing account.', $validated);
                return back()->withInput()->withErrors(['account_id' => 'Select an account to adjust.']);
            }
            $validated['from_account_id'] = null;
            $validated['to_account_id'] = null;
        }

        $validated['user_id'] = $request->user()->id;
        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['source'] = 'manual';

        $transaction = Transaction::create($validated);

        if (! $transaction->scheduled_item_id) {
            $scheduledItem = $this->createScheduledItemFromTransaction($transaction);
            if ($scheduledItem) {
                $transaction->update(['scheduled_item_id' => $scheduledItem->id]);
            }
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded.');
    }

    private function logTransactionIssue(Request $request, string $reason, array $validated): void
    {
        Log::warning('Transaction request blocked', [
            'user_id' => $request->user()?->id,
            'reason' => $reason,
            'input' => $request->except(['_token']),
            'validated' => $validated,
        ]);
    }

    private function createScheduledItemFromTransaction(Transaction $transaction): ?ScheduledItem
    {
        $type = $transaction->type;
        $isTransfer = in_array($type, ['transfer', 'credit_payment'], true);
        $isExpense = in_array($type, ['expense', 'credit_charge'], true);
        $isIncome = $type === 'income';

        if (! $isTransfer && ! $isExpense && ! $isIncome) {
            return null;
        }

        $data = [
            'user_id' => $transaction->user_id,
            'date' => $transaction->date,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency ?? 'USD',
            'status' => 'paid',
            'paid_at' => Carbon::now(),
            'actual_amount' => $transaction->amount,
            'note' => $transaction->memo,
        ];

        if ($isIncome) {
            $data['kind'] = 'income';
            $data['account_id'] = $transaction->to_account_id;
        } elseif ($isExpense) {
            $data['kind'] = 'expense';
            $data['account_id'] = $type === 'credit_charge'
                ? $transaction->account_id
                : $transaction->from_account_id;
        } else {
            $data['kind'] = 'transfer';
            $data['source_account_id'] = $transaction->from_account_id;
            $data['target_account_id'] = $transaction->to_account_id;
        }

        return ScheduledItem::create($data);
    }

    public function bulkReconcile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_ids' => ['required', 'array'],
            'transaction_ids.*' => ['integer'],
            'action' => ['required', 'in:reconcile,unreconcile'],
        ]);

        $transactions = Transaction::where('user_id', $request->user()->id)
            ->whereIn('id', $validated['transaction_ids'])
            ->get();

        $skipped = 0;
        foreach ($transactions as $transaction) {
            if ($this->monthLockService->isLocked($request->user(), $transaction->date)) {
                $skipped++;
                continue;
            }

            $transaction->update([
                'is_reconciled' => $validated['action'] === 'reconcile',
                'reconciled_at' => $validated['action'] === 'reconcile' ? now() : null,
            ]);
        }

        $message = $validated['action'] === 'reconcile' ? 'Transactions marked as reconciled.' : 'Transactions marked as unreconciled.';
        if ($skipped > 0) {
            $message .= ' Some entries were skipped due to locked months.';
        }

        return redirect()->route('transactions.index', $request->only(['month', 'account_id', 'reconciled']))
            ->with('success', $message);
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        if ($this->monthLockService->isLocked($request->user(), $transaction->date)) {
            return back()->with('error', 'This month is locked. Unlock it to delete transactions.');
        }

        $transaction->delete();

        return redirect()
            ->route('transactions.index', $request->only(['month', 'account_id', 'reconciled']))
            ->with('success', 'Transaction deleted.');
    }
}

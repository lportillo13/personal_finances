<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\ScheduledItem;
use App\Models\Transaction;
use App\Services\CardCycleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CreditCardController extends Controller
{
    public function __construct(private CardCycleService $cardCycleService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $cards = CreditCard::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['account', 'autopayPayFrom', 'defaultFundingAccount'])
            ->orderBy('id', 'desc')
            ->get();

        $today = Carbon::today();
        $summaries = $cards->mapWithKeys(function (CreditCard $card) use ($today) {
            $account = $card->account;
            $currentCycle = $this->cardCycleService->getCurrentCycle($account, $today);
            $previousCycle = $this->cardCycleService->getPreviousCycle($account, $today);

            return [
                $card->id => [
                    'current_balance' => $this->cardCycleService->computeCurrentBalance($account, $today),
                    'current_cycle' => $currentCycle,
                    'current_statement_balance' => $this->cardCycleService->computeStatementBalance(
                        $account,
                        $currentCycle['period_start'],
                        $currentCycle['period_end']
                    ),
                    'previous_cycle' => $previousCycle,
                    'previous_statement_balance' => $this->cardCycleService->computeStatementBalance(
                        $account,
                        $previousCycle['period_start'],
                        $previousCycle['period_end']
                    ),
                ],
            ];
        });

        return view('credit_cards.index', compact('cards', 'summaries'));
    }

    public function show(Request $request, Account $account): View
    {
        $cardAccount = $this->authorizeCardAccount($request, $account);
        $creditCard = $cardAccount->creditCard;
        $monthString = $request->input('month', Carbon::today()->format('Y-m'));
        $month = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $currentCycle = $this->cardCycleService->getCurrentCycle($cardAccount, Carbon::today());
        $previousCycle = $this->cardCycleService->getPreviousCycle($cardAccount, Carbon::today());
        $currentBalance = $this->cardCycleService->computeCurrentBalance($cardAccount, Carbon::today());
        $currentStatementBalance = $this->cardCycleService->computeStatementBalance(
            $cardAccount,
            $currentCycle['period_start'],
            $currentCycle['period_end']
        );
        $previousStatementBalance = $this->cardCycleService->computeStatementBalance(
            $cardAccount,
            $previousCycle['period_start'],
            $previousCycle['period_end']
        );

        $transactions = Transaction::where('user_id', $request->user()->id)
            ->where(function ($query) use ($cardAccount) {
                $query->where('account_id', $cardAccount->id)
                    ->orWhere('to_account_id', $cardAccount->id)
                    ->orWhere('from_account_id', $cardAccount->id);
            })
            ->whereBetween('date', [$month, $monthEnd])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('credit_cards.show', [
            'creditCard' => $creditCard,
            'account' => $cardAccount,
            'currentCycle' => $currentCycle,
            'previousCycle' => $previousCycle,
            'currentBalance' => $currentBalance,
            'currentStatementBalance' => $currentStatementBalance,
            'previousStatementBalance' => $previousStatementBalance,
            'transactions' => $transactions,
            'month' => $month,
        ]);
    }

    public function create(Request $request): View
    {
        $payFromAccounts = Account::where('user_id', $request->user()->id)
            ->whereIn('type', ['income', 'cash'])
            ->orderBy('name')
            ->get();

        return view('credit_cards.create', compact('payFromAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $data['current_amount'] = $data['current_amount'] ?? null;

        $account = Account::create([
            'user_id' => $request->user()->id,
            'name' => $data['account_name'],
            'type' => 'credit_card',
            'currency' => $data['currency'] ?? 'USD',
            'is_active' => true,
            'is_funding' => false,
        ]);

        $data['account_id'] = $account->id;
        unset($data['account_name'], $data['currency']);

        $this->validateFundingAccounts($request, $data);
        $data['autopay_enabled'] = $request->boolean('autopay_enabled');

        CreditCard::create($data);

        return redirect()->route('credit-cards.index')->with('status', 'Credit card saved.');
    }

    public function edit(Request $request, CreditCard $creditCard): View
    {
        $this->authorizeCard($creditCard, $request);

        $payFromAccounts = Account::where('user_id', $request->user()->id)
            ->whereIn('type', ['income', 'cash'])
            ->orderBy('name')
            ->get();

        $creditCard->load('account');

        return view('credit_cards.edit', compact('creditCard', 'payFromAccounts'));
    }

    public function update(Request $request, CreditCard $creditCard): RedirectResponse
    {
        $this->authorizeCard($creditCard, $request);

        $data = $this->validatedData($request);

        $data['current_amount'] = $data['current_amount'] ?? null;

        $this->validateFundingAccounts($request, $data);
        $data['autopay_enabled'] = $request->boolean('autopay_enabled');

        $creditCard->account->update([
            'name' => $data['account_name'],
            'currency' => $data['currency'] ?? $creditCard->account->currency ?? 'USD',
        ]);

        $data['account_id'] = $creditCard->account_id;
        unset($data['account_name'], $data['currency']);

        $creditCard->update($data);

        return redirect()->route('credit-cards.index')->with('status', 'Credit card updated.');
    }

    public function destroy(Request $request, CreditCard $creditCard): RedirectResponse
    {
        $this->authorizeCard($creditCard, $request);

        $creditCard->delete();

        return redirect()->route('credit-cards.index')->with('status', 'Credit card deleted.');
    }

    public function payForm(Request $request, Account $account): View
    {
        $cardAccount = $this->authorizeCardAccount($request, $account);
        $creditCard = $cardAccount->creditCard;
        $previousCycle = $this->cardCycleService->getPreviousCycle($cardAccount, Carbon::today());
        $currentCycle = $this->cardCycleService->getCurrentCycle($cardAccount, Carbon::today());
        $previousStatementBalance = $this->cardCycleService->computeStatementBalance(
            $cardAccount,
            $previousCycle['period_start'],
            $previousCycle['period_end']
        );
        $currentBalance = $this->cardCycleService->computeCurrentBalance($cardAccount, Carbon::today());

        $fundingAccounts = Account::where('user_id', $request->user()->id)
            ->whereIn('type', ['income', 'cash'])
            ->orderBy('name')
            ->get();

        return view('credit_cards.pay', [
            'account' => $cardAccount,
            'creditCard' => $creditCard,
            'fundingAccounts' => $fundingAccounts,
            'previousCycle' => $previousCycle,
            'currentCycle' => $currentCycle,
            'previousStatementBalance' => $previousStatementBalance,
            'currentBalance' => $currentBalance,
        ]);
    }

    public function pay(Request $request, Account $account): RedirectResponse
    {
        $cardAccount = $this->authorizeCardAccount($request, $account);
        $creditCard = $cardAccount->creditCard;
        $previousCycle = $this->cardCycleService->getPreviousCycle($cardAccount, Carbon::today());
        $currentCycle = $this->cardCycleService->getCurrentCycle($cardAccount, Carbon::today());
        $previousStatementBalance = $this->cardCycleService->computeStatementBalance(
            $cardAccount,
            $previousCycle['period_start'],
            $previousCycle['period_end']
        );
        $currentBalance = $this->cardCycleService->computeCurrentBalance($cardAccount, Carbon::today());

        $fundingAccountIds = Account::where('user_id', $request->user()->id)
            ->whereIn('type', ['income', 'cash'])
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'funding_account_id' => ['required', Rule::in($fundingAccountIds)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'apply_to_cycle' => ['required', Rule::in(['previous', 'current'])],
            'memo' => ['nullable', 'string', 'max:255'],
        ]);

        $cycle = $data['apply_to_cycle'] === 'previous' ? $previousCycle : $currentCycle;
        $defaultAmount = $data['apply_to_cycle'] === 'previous' ? $previousStatementBalance : $currentBalance;
        $amount = (float) $data['amount'];

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'date' => $data['payment_date'],
            'type' => 'transfer',
            'from_account_id' => $data['funding_account_id'],
            'to_account_id' => $cardAccount->id,
            'amount' => $amount,
            'currency' => $cardAccount->currency ?? 'USD',
            'memo' => $data['memo'],
            'statement_period_start' => $cycle['period_start']->toDateString(),
            'statement_period_end' => $cycle['period_end']->toDateString(),
        ]);

        $scheduledItem = ScheduledItem::where('user_id', $request->user()->id)
            ->where('kind', 'transfer')
            ->whereDate('date', $cycle['due_date'])
            ->where('target_account_id', $cardAccount->id)
            ->first();

        if ($scheduledItem) {
            $scheduledItem->update([
                'status' => 'paid',
                'paid_at' => now(),
                'actual_amount' => $amount,
                'note' => $data['memo'] ?? $scheduledItem->note,
            ]);

            $transaction->update(['scheduled_item_id' => $scheduledItem->id]);
        }

        $message = $scheduledItem ? 'Payment recorded and scheduled item marked paid.' : 'Payment recorded (no matching scheduled item).';

        if ($defaultAmount && $amount < $defaultAmount) {
            $message .= ' Note: amount is less than the statement balance.';
        }

        return redirect()->route('credit-cards.show', $cardAccount)->with('status', $message);
    }

    public function createStatementPayment(Request $request, Account $account): RedirectResponse
    {
        $cardAccount = $this->authorizeCardAccount($request, $account);
        $creditCard = $cardAccount->creditCard;
        $previousCycle = $this->cardCycleService->getPreviousCycle($cardAccount, Carbon::today());
        $statementBalance = $this->cardCycleService->computeStatementBalance(
            $cardAccount,
            $previousCycle['period_start'],
            $previousCycle['period_end']
        );

        if ($statementBalance <= 0) {
            return back()->with('status', 'No statement balance due for the previous cycle.');
        }

        $fundingAccount = $this->resolveFundingAccount($request->user()->id, $creditCard);
        $existing = ScheduledItem::where('user_id', $request->user()->id)
            ->where('kind', 'transfer')
            ->whereDate('date', $previousCycle['due_date'])
            ->where('target_account_id', $cardAccount->id)
            ->first();

        if ($existing) {
            $existing->update(['amount' => $statementBalance, 'source_account_id' => $fundingAccount?->id]);

            return back()->with('status', 'Existing statement payment scheduled item updated.');
        }

        $category = Category::firstOrCreate(
            ['user_id' => $request->user()->id, 'name' => 'Credit Card Payment'],
            ['kind' => 'expense']
        );

        ScheduledItem::create([
            'user_id' => $request->user()->id,
            'date' => $previousCycle['due_date'],
            'kind' => 'transfer',
            'amount' => $statementBalance,
            'currency' => $cardAccount->currency ?? 'USD',
            'source_account_id' => $fundingAccount?->id,
            'target_account_id' => $cardAccount->id,
            'category_id' => $category->id,
            'status' => ScheduledItem::pendingStatus(),
            'note' => 'Statement payment',
        ]);

        return back()->with('status', 'Statement payment scheduled.');
    }

    protected function authorizeCard(CreditCard $creditCard, Request $request): void
    {
        abort_if($creditCard->account->user_id !== $request->user()->id, 403);
    }

    protected function authorizeCardAccount(Request $request, Account $account): Account
    {
        abort_if($account->user_id !== $request->user()->id, 403);
        abort_if($account->type !== 'credit_card' || ! $account->creditCard, 404);

        return $account->load(['creditCard']);
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'issuer_name' => ['nullable', 'string', 'max:255'],
            'last4' => ['nullable', 'string', 'size:4'],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'payment_due_day' => ['nullable', 'integer', 'between:1,31'],
            'statement_close_day' => ['nullable', 'integer', 'between:1,31'],
            'minimum_payment' => ['nullable', 'numeric', 'min:0'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'autopay_enabled' => ['nullable', 'boolean'],
            'autopay_mode' => ['nullable', Rule::in(['minimum', 'statement', 'fixed'])],
            'autopay_fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'autopay_pay_from_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'default_funding_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function validateFundingAccounts(Request $request, array $data): void
    {
        $fundingAccounts = Account::where('user_id', $request->user()->id)
            ->whereIn('type', ['income', 'cash'])
            ->pluck('id')
            ->all();

        if (! empty($data['autopay_pay_from_account_id'])) {
            abort_unless(in_array($data['autopay_pay_from_account_id'], $fundingAccounts), 403);
        }

        if (! empty($data['default_funding_account_id'])) {
            abort_unless(in_array($data['default_funding_account_id'], $fundingAccounts), 403);
        }
    }

    protected function resolveFundingAccount(int $userId, CreditCard $creditCard): ?Account
    {
        $account = null;

        if ($creditCard->default_funding_account_id) {
            $account = Account::where('user_id', $userId)
                ->where('id', $creditCard->default_funding_account_id)
                ->whereIn('type', ['income', 'cash'])
                ->first();
        }

        if (! $account) {
            $account = Account::where('user_id', $userId)
                ->whereIn('type', ['income', 'cash'])
                ->orderBy('is_funding', 'desc')
                ->orderBy('name')
                ->first();
        }

        return $account;
    }
}

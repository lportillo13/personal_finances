<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $monthString = $request->input('month', Carbon::today()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $accounts = Account::where('user_id', $user->id)->orderBy('name')->get();
        $accountId = $accounts->firstWhere('id', (int) $request->input('account_id'))?->id;

        $transactions = Transaction::where('user_id', $user->id)
            ->when($accountId, function ($query) use ($accountId) {
                $query->where(function ($query) use ($accountId) {
                    $query->where('from_account_id', $accountId)
                        ->orWhere('to_account_id', $accountId)
                        ->orWhere('account_id', $accountId);
                });
            })
            ->whereBetween('date', [$start, $end])
            ->with(['fromAccount', 'toAccount', 'account', 'scheduledItem.recurringRule'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'month' => $start,
            'selectedAccountId' => $accountId,
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
        $accountIds = Account::where('user_id', $request->user()->id)->pluck('id')->all();

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

        if ($type === 'income') {
            if (empty($validated['to_account_id'])) {
                return back()->withInput()->withErrors(['to_account_id' => 'Select an account for this income.']);
            }
            $validated['from_account_id'] = null;
            $validated['account_id'] = null;
        } elseif ($type === 'expense') {
            if (empty($validated['from_account_id'])) {
                return back()->withInput()->withErrors(['from_account_id' => 'Select a source account for this expense.']);
            }
            $validated['to_account_id'] = null;
            $validated['account_id'] = null;
        } elseif ($type === 'transfer') {
            if (empty($validated['from_account_id']) || empty($validated['to_account_id'])) {
                return back()->withInput()->withErrors(['to_account_id' => 'Transfers need both source and destination accounts.']);
            }
            if ($validated['from_account_id'] === $validated['to_account_id']) {
                return back()->withInput()->withErrors(['to_account_id' => 'Source and destination cannot match.']);
            }
            $validated['account_id'] = null;
        } elseif ($type === 'credit_charge') {
            if (empty($validated['account_id'])) {
                return back()->withInput()->withErrors(['account_id' => 'Choose the credit card for this charge.']);
            }
            $validated['from_account_id'] = null;
            $validated['to_account_id'] = null;
        } elseif ($type === 'credit_payment') {
            if (empty($validated['from_account_id']) || empty($validated['to_account_id'])) {
                return back()->withInput()->withErrors(['to_account_id' => 'Payments need a funding account and card.']);
            }
            if ($validated['from_account_id'] === $validated['to_account_id']) {
                return back()->withInput()->withErrors(['to_account_id' => 'Funding account and card must differ.']);
            }
            $validated['account_id'] = null;
        } elseif ($type === 'adjustment') {
            if (empty($validated['account_id'])) {
                return back()->withInput()->withErrors(['account_id' => 'Select an account to adjust.']);
            }
            $validated['from_account_id'] = null;
            $validated['to_account_id'] = null;
        }

        $validated['user_id'] = $request->user()->id;
        $validated['currency'] = $validated['currency'] ?? 'USD';

        Transaction::create($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded.');
    }
}

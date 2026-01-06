<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CreditCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditCardController extends Controller
{
    public function index(Request $request): View
    {
        $cards = CreditCard::whereHas('account', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->with(['account', 'autopayPayFrom'])->orderBy('id', 'desc')->get();

        return view('credit_cards.index', compact('cards'));
    }

    public function create(Request $request): View
    {
        $accounts = Account::where('user_id', $request->user()->id)
            ->where('type', 'credit_card')
            ->doesntHave('creditCard')
            ->orderBy('name')
            ->get();
        $payFromAccounts = Account::where('user_id', $request->user()->id)
            ->whereIn('type', ['income', 'cash'])
            ->orderBy('name')
            ->get();

        return view('credit_cards.create', compact('accounts', 'payFromAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'issuer_name' => ['nullable', 'string', 'max:255'],
            'last4' => ['nullable', 'string', 'size:4'],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'statement_close_day' => ['nullable', 'integer', 'between:1,31'],
            'autopay_enabled' => ['nullable', 'boolean'],
            'autopay_pay_from_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $account = Account::where('id', $data['account_id'])
            ->where('user_id', $request->user()->id)
            ->where('type', 'credit_card')
            ->firstOrFail();

        $data['autopay_enabled'] = $request->boolean('autopay_enabled');

        if (! empty($data['autopay_pay_from_account_id'])) {
            Account::where('id', $data['autopay_pay_from_account_id'])
                ->where('user_id', $request->user()->id)
                ->whereIn('type', ['income', 'cash'])
                ->firstOrFail();
        }

        CreditCard::create($data);

        return redirect()->route('credit-cards.index')->with('status', 'Credit card saved.');
    }

    public function edit(Request $request, CreditCard $creditCard): View
    {
        $this->authorizeCard($creditCard, $request);

        $accounts = Account::where('user_id', $request->user()->id)
            ->where('type', 'credit_card')
            ->where(function ($query) use ($creditCard) {
                $query->whereDoesntHave('creditCard')->orWhere('id', $creditCard->account_id);
            })
            ->orderBy('name')
            ->get();

        $payFromAccounts = Account::where('user_id', $request->user()->id)
            ->whereIn('type', ['income', 'cash'])
            ->orderBy('name')
            ->get();

        return view('credit_cards.edit', compact('creditCard', 'accounts', 'payFromAccounts'));
    }

    public function update(Request $request, CreditCard $creditCard): RedirectResponse
    {
        $this->authorizeCard($creditCard, $request);

        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'issuer_name' => ['nullable', 'string', 'max:255'],
            'last4' => ['nullable', 'string', 'size:4'],
            'due_day' => ['required', 'integer', 'between:1,31'],
            'statement_close_day' => ['nullable', 'integer', 'between:1,31'],
            'autopay_enabled' => ['nullable', 'boolean'],
            'autopay_pay_from_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        Account::where('id', $data['account_id'])
            ->where('user_id', $request->user()->id)
            ->where('type', 'credit_card')
            ->firstOrFail();

        $data['autopay_enabled'] = $request->boolean('autopay_enabled');

        if (! empty($data['autopay_pay_from_account_id'])) {
            Account::where('id', $data['autopay_pay_from_account_id'])
                ->where('user_id', $request->user()->id)
                ->whereIn('type', ['income', 'cash'])
                ->firstOrFail();
        }

        $creditCard->update($data);

        return redirect()->route('credit-cards.index')->with('status', 'Credit card updated.');
    }

    public function destroy(Request $request, CreditCard $creditCard): RedirectResponse
    {
        $this->authorizeCard($creditCard, $request);

        $creditCard->delete();

        return redirect()->route('credit-cards.index')->with('status', 'Credit card deleted.');
    }

    protected function authorizeCard(CreditCard $creditCard, Request $request): void
    {
        abort_if($creditCard->account->user_id !== $request->user()->id, 403);
    }
}

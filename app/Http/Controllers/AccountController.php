<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ScheduledItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $accounts = Account::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('accounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,cash,credit_card'],
            'currency' => ['nullable', 'string', 'size:3'],
            'is_active' => ['nullable', 'boolean'],
            'is_funding' => ['nullable', 'boolean'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_funding'] = $request->boolean('is_funding', false);

        if ($data['is_funding']) {
            Account::where('user_id', $data['user_id'])->update(['is_funding' => false]);
        }

        Account::create($data);

        return redirect()->route('accounts.index')->with('status', 'Account created.');
    }

    public function edit(Account $account): View
    {
        $this->authorizeAccount($account);

        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $this->authorizeAccount($account);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,cash,credit_card'],
            'currency' => ['nullable', 'string', 'size:3'],
            'is_active' => ['nullable', 'boolean'],
            'is_funding' => ['nullable', 'boolean'],
        ]);

        $data['currency'] = $data['currency'] ?? 'USD';
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_funding'] = $request->boolean('is_funding', false);

        if ($data['is_funding']) {
            Account::where('user_id', $account->user_id)->update(['is_funding' => false]);
        }

        $account->update($data);

        return redirect()->route('accounts.index')->with('status', 'Account updated.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorizeAccount($account);

        if ($account->creditCard()->exists()) {
            return redirect()
                ->route('accounts.index')
                ->with('error', 'Cannot delete an account that has a linked credit card.');
        }

        $hasScheduled = ScheduledItem::where('user_id', $account->user_id)
            ->where(function ($query) use ($account) {
                $query->where('account_id', $account->id)
                    ->orWhere('source_account_id', $account->id)
                    ->orWhere('target_account_id', $account->id);
            })
            ->exists();

        if ($hasScheduled) {
            return redirect()
                ->route('accounts.index')
                ->with('error', 'Cannot delete an account that has scheduled items.');
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('status', 'Account deleted.');
    }

    public function setFunding(Account $account, Request $request): RedirectResponse
    {
        $this->authorizeAccount($account);

        Account::where('user_id', $account->user_id)->update(['is_funding' => false]);
        $account->update(['is_funding' => true]);

        return redirect()->route('accounts.index')->with('status', 'Funding account updated.');
    }

    protected function authorizeAccount(Account $account): void
    {
        abort_if($account->user_id !== auth()->id(), 403);
    }
}

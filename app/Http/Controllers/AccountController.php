<?php

namespace App\Http\Controllers;

use App\Models\Account;
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

        $account->delete();

        return redirect()->route('accounts.index')->with('status', 'Account deleted.');
    }

    protected function authorizeAccount(Account $account): void
    {
        abort_if($account->user_id !== auth()->id(), 403);
    }
}

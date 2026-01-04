<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::with('creditCard')
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $autopayAccounts = Account::where('user_id', auth()->id())->orderBy('name')->get();

        return view('accounts.create', compact('autopayAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'cash', 'credit_card'])],
            'currency' => ['nullable', 'string', 'max:3'],
            'is_active' => ['sometimes', 'boolean'],
            'due_day' => ['required_if:type,credit_card', 'nullable', 'integer', 'between:1,31'],
            'statement_close_day' => ['nullable', 'integer', 'between:1,31'],
            'autopay' => ['sometimes', 'boolean'],
            'autopay_account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')->where('user_id', auth()->id()),
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $account = Account::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency' => $validated['currency'] ?? 'USD',
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($account->type === 'credit_card') {
            $account->creditCard()->create([
                'due_day' => $validated['due_day'] ?? 1,
                'statement_close_day' => $validated['statement_close_day'] ?? null,
                'autopay' => $request->boolean('autopay'),
                'autopay_account_id' => $validated['autopay_account_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        return Redirect::route('accounts.index')->with('status', 'Account created.');
    }

    public function edit(Account $account): View
    {
        $this->authorizeAccount($account);

        $autopayAccounts = Account::where('user_id', auth()->id())->where('id', '!=', $account->id)->orderBy('name')->get();

        return view('accounts.edit', compact('account', 'autopayAccounts'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $this->authorizeAccount($account);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'cash', 'credit_card'])],
            'currency' => ['nullable', 'string', 'max:3'],
            'is_active' => ['sometimes', 'boolean'],
            'due_day' => ['required_if:type,credit_card', 'nullable', 'integer', 'between:1,31'],
            'statement_close_day' => ['nullable', 'integer', 'between:1,31'],
            'autopay' => ['sometimes', 'boolean'],
            'autopay_account_id' => [
                'nullable',
                Rule::exists('accounts', 'id')->where('user_id', auth()->id()),
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $account->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency' => $validated['currency'] ?? 'USD',
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($account->type === 'credit_card') {
            $account->creditCard()->updateOrCreate([], [
                'due_day' => $validated['due_day'] ?? 1,
                'statement_close_day' => $validated['statement_close_day'] ?? null,
                'autopay' => $request->boolean('autopay'),
                'autopay_account_id' => $validated['autopay_account_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        } else {
            $account->creditCard()->delete();
        }

        return Redirect::route('accounts.index')->with('status', 'Account updated.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorizeAccount($account);

        $account->delete();

        return Redirect::route('accounts.index')->with('status', 'Account deleted.');
    }

    protected function authorizeAccount(Account $account): void
    {
        abort_if($account->user_id !== auth()->id(), 403);
    }
}

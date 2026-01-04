<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecurringRuleController extends Controller
{
    public function index(): View
    {
        $rules = RecurringRule::with(['account', 'category'])
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('recurring-rules.index', compact('rules'));
    }

    public function create(): View
    {
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();
        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();

        return view('recurring-rules.create', compact('accounts', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRule($request);

        RecurringRule::create($validated + [
            'user_id' => auth()->id(),
            'next_run_on' => $validated['next_run_on'] ?? $validated['start_date'],
            'is_active' => $request->boolean('is_active', true),
            'currency' => $validated['currency'] ?? 'USD',
            'interval' => $validated['interval'] ?? 1,
        ]);

        return Redirect::route('recurring-rules.index')->with('status', 'Recurring rule created.');
    }

    public function edit(RecurringRule $recurringRule): View
    {
        $this->authorizeRule($recurringRule);
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();
        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();

        return view('recurring-rules.edit', [
            'rule' => $recurringRule,
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, RecurringRule $recurringRule): RedirectResponse
    {
        $this->authorizeRule($recurringRule);

        $validated = $this->validateRule($request);

        $recurringRule->update($validated + [
            'is_active' => $request->boolean('is_active', true),
            'currency' => $validated['currency'] ?? 'USD',
            'interval' => $validated['interval'] ?? 1,
        ]);

        return Redirect::route('recurring-rules.index')->with('status', 'Recurring rule updated.');
    }

    public function destroy(RecurringRule $recurringRule): RedirectResponse
    {
        $this->authorizeRule($recurringRule);
        $recurringRule->delete();

        return Redirect::route('recurring-rules.index')->with('status', 'Recurring rule deleted.');
    }

    protected function validateRule(Request $request): array
    {
        $accountRule = Rule::exists('accounts', 'id')->where('user_id', auth()->id());
        $categoryRule = Rule::exists('categories', 'id')->where('user_id', auth()->id());

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'direction' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'source_account_id' => ['nullable', $accountRule],
            'target_account_id' => ['nullable', $accountRule],
            'account_id' => ['nullable', $accountRule],
            'category_id' => ['nullable', $categoryRule],
            'frequency' => ['required', Rule::in(['weekly', 'biweekly', 'semimonthly', 'monthly'])],
            'interval' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'occurrences_total' => ['nullable', 'integer', 'min:1'],
            'occurrences_remaining' => ['nullable', 'integer', 'min:0'],
            'next_run_on' => ['sometimes', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    protected function authorizeRule(RecurringRule $rule): void
    {
        abort_if($rule->user_id !== auth()->id(), 403);
    }
}

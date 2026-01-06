<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringRuleController extends Controller
{
    public function index(Request $request): View
    {
        $rules = RecurringRule::where('user_id', $request->user()->id)
            ->with(['account', 'sourceAccount', 'targetAccount', 'category'])
            ->orderBy('name')
            ->get();

        return view('recurring_rules.index', compact('rules'));
    }

    public function create(Request $request): View
    {
        return $this->formView(new RecurringRule(['start_date' => now()->toDateString(), 'next_run_on' => now()->toDateString()]), $request);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['user_id'] = $request->user()->id;
        $data['next_run_on'] = $data['next_run_on'] ?? $data['start_date'];
        $data['occurrences_remaining'] = $data['occurrences_remaining'] ?? $data['occurrences_total'];

        RecurringRule::create($data);

        return redirect()->route('recurring-rules.index')->with('status', 'Recurring rule created.');
    }

    public function edit(Request $request, RecurringRule $recurringRule): View
    {
        $this->authorizeRule($recurringRule);

        return $this->formView($recurringRule, $request);
    }

    public function update(Request $request, RecurringRule $recurringRule): RedirectResponse
    {
        $this->authorizeRule($recurringRule);

        $data = $this->validateData($request);
        $data['occurrences_remaining'] = $data['occurrences_remaining'] ?? $recurringRule->occurrences_remaining;

        $recurringRule->update($data);

        return redirect()->route('recurring-rules.index')->with('status', 'Recurring rule updated.');
    }

    public function destroy(RecurringRule $recurringRule): RedirectResponse
    {
        $this->authorizeRule($recurringRule);

        $recurringRule->delete();

        return redirect()->route('recurring-rules.index')->with('status', 'Recurring rule deleted.');
    }

    protected function formView(RecurringRule $rule, Request $request): View
    {
        $accounts = Account::where('user_id', $request->user()->id)->orderBy('name')->get();
        $categories = Category::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('recurring_rules.form', compact('rule', 'accounts', 'categories'));
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'in:income,expense,transfer'],
            'amount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'source_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'target_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'frequency' => ['required', 'in:weekly,biweekly,semimonthly,monthly'],
            'interval' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'occurrences_total' => ['nullable', 'integer', 'min:1'],
            'occurrences_remaining' => ['nullable', 'integer', 'min:1'],
            'next_run_on' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'monthly_day' => ['nullable', 'integer', 'between:1,31'],
            'semimonthly_day_1' => ['nullable', 'integer', 'between:1,31'],
            'semimonthly_day_2' => ['nullable', 'integer', 'between:1,31'],
        ]);

        $data['currency'] = $data['currency'] ?? 'USD';
        $data['interval'] = $data['interval'] ?? 1;
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['frequency'] === 'semimonthly' && empty($data['semimonthly_day_1']) && empty($data['semimonthly_day_2'])) {
            abort(422, 'Provide at least one semimonthly day.');
        }

        if (empty($data['next_run_on'])) {
            $data['next_run_on'] = $data['start_date'];
        }

        $this->validateOwnership($data, $request);

        return $data;
    }

    protected function validateOwnership(array $data, Request $request): void
    {
        foreach (['account_id', 'source_account_id', 'target_account_id'] as $accountKey) {
            if (! empty($data[$accountKey])) {
                Account::where('id', $data[$accountKey])->where('user_id', $request->user()->id)->firstOrFail();
            }
        }

        if (! empty($data['category_id'])) {
            Category::where('id', $data['category_id'])->where('user_id', $request->user()->id)->firstOrFail();
        }
    }

    protected function authorizeRule(RecurringRule $recurringRule): void
    {
        abort_if($recurringRule->user_id !== auth()->id(), 403);
    }
}

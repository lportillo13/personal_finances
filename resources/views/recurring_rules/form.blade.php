@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Kind</label>
        <select name="kind" class="form-select" required>
            @foreach (['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer'] as $value => $label)
                <option value="{{ $value }}" @selected(old('kind', $rule->kind ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $rule->amount ?? '') }}" required>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-3">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" class="form-control" value="{{ old('currency', $rule->currency ?? 'USD') }}" maxlength="3">
    </div>
    <div class="col-md-3">
        <label class="form-label">Account</label>
        <select name="account_id" class="form-select">
            <option value="">Select</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('account_id', $rule->account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Source Account</label>
        <select name="source_account_id" class="form-select">
            <option value="">Select</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('source_account_id', $rule->source_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Target Account</label>
        <select name="target_account_id" class="form-select">
            <option value="">Select</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('target_account_id', $rule->target_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <option value="">Select</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $rule->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Frequency</label>
        <select name="frequency" class="form-select" required>
            @foreach (['weekly' => 'Weekly', 'biweekly' => 'Biweekly', 'semimonthly' => 'Semimonthly', 'monthly' => 'Monthly'] as $value => $label)
                <option value="{{ $value }}" @selected(old('frequency', $rule->frequency ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Interval</label>
        <input type="number" name="interval" min="1" class="form-control" value="{{ old('interval', $rule->interval ?? 1) }}">
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label">Monthly Day</label>
        <input type="number" name="monthly_day" min="1" max="31" class="form-control" value="{{ old('monthly_day', $rule->monthly_day ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Semimonthly Day 1</label>
        <input type="number" name="semimonthly_day_1" min="1" max="31" class="form-control" value="{{ old('semimonthly_day_1', $rule->semimonthly_day_1 ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Semimonthly Day 2</label>
        <input type="number" name="semimonthly_day_2" min="1" max="31" class="form-control" value="{{ old('semimonthly_day_2', $rule->semimonthly_day_2 ?? '') }}">
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($rule->start_date)->toDateString()) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($rule->end_date)->toDateString()) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Next Run On</label>
        <input type="date" name="next_run_on" class="form-control" value="{{ old('next_run_on', optional($rule->next_run_on)->toDateString()) }}">
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label">Occurrences Total</label>
        <input type="number" name="occurrences_total" min="1" class="form-control" value="{{ old('occurrences_total', $rule->occurrences_total ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Occurrences Remaining</label>
        <input type="number" name="occurrences_remaining" min="1" class="form-control" value="{{ old('occurrences_remaining', $rule->occurrences_remaining ?? '') }}">
    </div>
</div>
<div class="form-check mt-3">
    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" @checked(old('is_active', $rule->is_active ?? true))>
    <label for="is_active" class="form-check-label">Active</label>
</div>
<div class="mt-3">
    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('recurring-rules.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

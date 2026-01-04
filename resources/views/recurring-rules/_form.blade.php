<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Direction</label>
        <select name="direction" class="form-select" required>
            @foreach(['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer'] as $value => $label)
                <option value="{{ $value }}" @selected(old('direction', $rule->direction ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $rule->amount ?? '') }}" required>
    </div>
</div>
<div class="row g-3 mt-3">
    <div class="col-md-3">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" class="form-control" value="{{ old('currency', $rule->currency ?? 'USD') }}" maxlength="3">
    </div>
    <div class="col-md-3">
        <label class="form-label">Account</label>
        <select name="account_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('account_id', $rule->account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Source account (transfers)</label>
        <select name="source_account_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('source_account_id', $rule->source_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Target account (transfers)</label>
        <select name="target_account_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('target_account_id', $rule->target_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row g-3 mt-3">
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $rule->category_id ?? '') == $category->id)>{{ $category->name }} ({{ $category->type }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Frequency</label>
        <select name="frequency" class="form-select" required>
            @foreach(['weekly' => 'Weekly', 'biweekly' => 'Biweekly', 'semimonthly' => 'Semimonthly', 'monthly' => 'Monthly'] as $value => $label)
                <option value="{{ $value }}" @selected(old('frequency', $rule->frequency ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Interval</label>
        <input type="number" name="interval" class="form-control" value="{{ old('interval', $rule->interval ?? 1) }}" min="1">
    </div>
</div>
<div class="row g-3 mt-3">
    <div class="col-md-4">
        <label class="form-label">Start date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($rule->start_date ?? null)->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">End date</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($rule->end_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Next run on</label>
        <input type="date" name="next_run_on" class="form-control" value="{{ old('next_run_on', optional($rule->next_run_on ?? ($rule->start_date ?? null))->format('Y-m-d')) }}">
    </div>
</div>
<div class="row g-3 mt-3">
    <div class="col-md-4">
        <label class="form-label">Total occurrences</label>
        <input type="number" name="occurrences_total" class="form-control" value="{{ old('occurrences_total', $rule->occurrences_total ?? '') }}" min="1">
    </div>
    <div class="col-md-4">
        <label class="form-label">Remaining occurrences</label>
        <input type="number" name="occurrences_remaining" class="form-control" value="{{ old('occurrences_remaining', $rule->occurrences_remaining ?? '') }}" min="0">
    </div>
    <div class="col-md-4 d-flex align-items-center">
        <div class="form-check mt-4">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" @checked(old('is_active', $rule->is_active ?? true))>
            <label for="is_active" class="form-check-label">Active</label>
        </div>
    </div>
</div>

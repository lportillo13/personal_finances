<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $account->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-select" required>
        @foreach(['income' => 'Income', 'cash' => 'Cash', 'credit_card' => 'Credit Card'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $account->type ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Currency</label>
    <input type="text" name="currency" class="form-control" value="{{ old('currency', $account->currency ?? 'USD') }}" maxlength="3">
</div>
<div class="form-check mb-3">
    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" @checked(old('is_active', $account->is_active ?? true))>
    <label for="is_active" class="form-check-label">Active</label>
</div>
<div class="border rounded p-3 mb-3">
    <h6>Credit card details (optional)</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Due day</label>
            <input type="number" name="due_day" class="form-control" value="{{ old('due_day', optional($account->creditCard)->due_day) }}" min="1" max="31">
        </div>
        <div class="col-md-6">
            <label class="form-label">Statement close day</label>
            <input type="number" name="statement_close_day" class="form-control" value="{{ old('statement_close_day', optional($account->creditCard)->statement_close_day) }}" min="1" max="31">
        </div>
    </div>
    <div class="form-check mt-3">
        <input type="checkbox" name="autopay" class="form-check-input" id="autopay" value="1" @checked(old('autopay', optional($account->creditCard)->autopay ?? false))>
        <label for="autopay" class="form-check-label">Enable autopay</label>
    </div>
    <div class="mt-3">
        <label class="form-label">Autopay account</label>
        <select name="autopay_account_id" class="form-select">
            <option value="">-- Select account --</option>
            @foreach(($autopayAccounts ?? collect()) as $option)
                <option value="{{ $option->id }}" @selected(old('autopay_account_id', optional($account->creditCard)->autopay_account_id) == $option->id)>
                    {{ $option->name }} ({{ ucfirst($option->type) }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="mt-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', optional($account->creditCard)->notes) }}</textarea>
    </div>
    <p class="text-muted mt-2 small">Credit card details are used when the account type is set to "Credit Card".</p>
</div>

@csrf
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $account->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-select" required>
        @foreach (['income' => 'Income', 'cash' => 'Cash', 'credit_card' => 'Credit Card'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $account->type ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Currency</label>
    <input type="text" name="currency" class="form-control" value="{{ old('currency', $account->currency ?? 'USD') }}" maxlength="3">
</div>
<div class="form-check mb-2">
    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" @checked(old('is_active', $account->is_active ?? true))>
    <label for="is_active" class="form-check-label">Active</label>
</div>
<div class="form-check mb-3">
    <input type="checkbox" name="is_funding" class="form-check-input" id="is_funding" value="1" @checked(old('is_funding', $account->is_funding ?? false))>
    <label for="is_funding" class="form-check-label">Set as income funding account</label>
</div>
<button class="btn btn-primary" type="submit">Save</button>
<a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">Cancel</a>

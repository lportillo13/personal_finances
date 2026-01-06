@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Name</label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $account->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="type">Type</label>
        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach (['income' => 'Income', 'cash' => 'Cash', 'credit_card' => 'Credit Card'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $account->type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label" for="currency">Currency</label>
        <input type="text" id="currency" name="currency" class="form-control @error('currency') is-invalid @enderror" value="{{ old('currency', $account->currency ?? 'USD') }}" maxlength="3">
        @error('currency')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" name="is_active" class="form-check-input @error('is_active') is-invalid @enderror" id="is_active" value="1" @checked(old('is_active', $account->is_active ?? true))>
            <label for="is_active" class="form-check-label">Active</label>
            @error('is_active')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" name="is_funding" class="form-check-input @error('is_funding') is-invalid @enderror" id="is_funding" value="1" @checked(old('is_funding', $account->is_funding ?? false))>
            <label for="is_funding" class="form-check-label">Set as income funding account</label>
            @error('is_funding')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

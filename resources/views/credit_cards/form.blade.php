@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="account_id">Credit Card Account</label>
        <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
            <option value="">Select account</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('account_id', $creditCard->account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
        @error('account_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="issuer_name">Issuer Name</label>
        <input type="text" name="issuer_name" id="issuer_name" class="form-control @error('issuer_name') is-invalid @enderror" value="{{ old('issuer_name', $creditCard->issuer_name ?? '') }}">
        @error('issuer_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label" for="last4">Last 4</label>
        <input type="text" name="last4" id="last4" maxlength="4" class="form-control @error('last4') is-invalid @enderror" value="{{ old('last4', $creditCard->last4 ?? '') }}">
        @error('last4')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="due_day">Due Day</label>
        <input type="number" name="due_day" id="due_day" min="1" max="31" class="form-control @error('due_day') is-invalid @enderror" value="{{ old('due_day', $creditCard->due_day ?? '') }}" required>
        @error('due_day')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="statement_close_day">Statement Close Day</label>
        <input type="number" name="statement_close_day" id="statement_close_day" min="1" max="31" class="form-control @error('statement_close_day') is-invalid @enderror" value="{{ old('statement_close_day', $creditCard->statement_close_day ?? '') }}">
        @error('statement_close_day')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" name="autopay_enabled" class="form-check-input @error('autopay_enabled') is-invalid @enderror" id="autopay_enabled" value="1" @checked(old('autopay_enabled', $creditCard->autopay_enabled ?? false))>
            <label for="autopay_enabled" class="form-check-label">Autopay enabled</label>
            @error('autopay_enabled')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="autopay_pay_from_account_id">Autopay Pay From</label>
        <select name="autopay_pay_from_account_id" id="autopay_pay_from_account_id" class="form-select @error('autopay_pay_from_account_id') is-invalid @enderror">
            <option value="">Select account</option>
            @foreach ($payFromAccounts as $account)
                <option value="{{ $account->id }}" @selected(old('autopay_pay_from_account_id', $creditCard->autopay_pay_from_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
        @error('autopay_pay_from_account_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-12">
        <label class="form-label" for="notes">Notes</label>
        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $creditCard->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('credit-cards.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

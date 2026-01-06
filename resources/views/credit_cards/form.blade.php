@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="account_name">Card Name</label>
        <input
            type="text"
            name="account_name"
            id="account_name"
            class="form-control @error('account_name') is-invalid @enderror"
            value="{{ old('account_name', optional($creditCard->account)->name ?? '') }}"
            required
        >
        @error('account_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="currency">Currency</label>
        <input
            type="text"
            name="currency"
            id="currency"
            maxlength="3"
            class="form-control @error('currency') is-invalid @enderror"
            value="{{ old('currency', optional($creditCard->account)->currency ?? 'USD') }}"
        >
        @error('currency')
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
        <label class="form-label" for="payment_due_day">Payment Due Day</label>
        <input type="number" name="payment_due_day" id="payment_due_day" min="1" max="31" class="form-control @error('payment_due_day') is-invalid @enderror" value="{{ old('payment_due_day', $creditCard->payment_due_day ?? '') }}">
        @error('payment_due_day')
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
    <div class="col-md-4">
        <label class="form-label" for="minimum_payment">Minimum Payment</label>
        <input type="number" step="0.01" name="minimum_payment" id="minimum_payment" class="form-control @error('minimum_payment') is-invalid @enderror" value="{{ old('minimum_payment', $creditCard->minimum_payment ?? '') }}">
        @error('minimum_payment')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="current_amount">Current Amount Due</label>
        <input
            type="number"
            step="0.01"
            name="current_amount"
            id="current_amount"
            class="form-control @error('current_amount') is-invalid @enderror"
            value="{{ old('current_amount', $creditCard->current_amount ?? '') }}"
        >
        @error('current_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <div class="form-check">
            <input type="checkbox" name="autopay_enabled" class="form-check-input @error('autopay_enabled') is-invalid @enderror" id="autopay_enabled" value="1" @checked(old('autopay_enabled', $creditCard->autopay_enabled ?? false))>
            <label for="autopay_enabled" class="form-check-label">Autopay enabled</label>
            @error('autopay_enabled')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label" for="autopay_mode">Autopay Mode</label>
        <select name="autopay_mode" id="autopay_mode" class="form-select @error('autopay_mode') is-invalid @enderror">
            <option value="">Select mode</option>
            <option value="minimum" @selected(old('autopay_mode', $creditCard->autopay_mode ?? '') === 'minimum')>Minimum</option>
            <option value="statement" @selected(old('autopay_mode', $creditCard->autopay_mode ?? '') === 'statement')>Statement</option>
            <option value="fixed" @selected(old('autopay_mode', $creditCard->autopay_mode ?? '') === 'fixed')>Fixed amount</option>
        </select>
        @error('autopay_mode')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="autopay_fixed_amount">Autopay Fixed Amount</label>
        <input type="number" step="0.01" name="autopay_fixed_amount" id="autopay_fixed_amount" class="form-control @error('autopay_fixed_amount') is-invalid @enderror" value="{{ old('autopay_fixed_amount', $creditCard->autopay_fixed_amount ?? '') }}">
        @error('autopay_fixed_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
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
    <div class="col-md-6">
        <label class="form-label" for="default_funding_account_id">Default Funding Account</label>
        <select name="default_funding_account_id" id="default_funding_account_id" class="form-select @error('default_funding_account_id') is-invalid @enderror">
            <option value="">Select account</option>
            @foreach ($payFromAccounts as $account)
                <option value="{{ $account->id }}" @selected(old('default_funding_account_id', $creditCard->default_funding_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
        @error('default_funding_account_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
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

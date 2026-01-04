@csrf
<div class="mb-3">
    <label class="form-label">Credit Card Account</label>
    <select name="account_id" class="form-select" required>
        <option value="">Select account</option>
        @foreach ($accounts as $account)
            <option value="{{ $account->id }}" @selected(old('account_id', $creditCard->account_id ?? '') == $account->id)>{{ $account->name }}</option>
        @endforeach
    </select>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Issuer Name</label>
        <input type="text" name="issuer_name" class="form-control" value="{{ old('issuer_name', $creditCard->issuer_name ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Last 4</label>
        <input type="text" name="last4" maxlength="4" class="form-control" value="{{ old('last4', $creditCard->last4 ?? '') }}">
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label">Due Day</label>
        <input type="number" name="due_day" min="1" max="31" class="form-control" value="{{ old('due_day', $creditCard->due_day ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Statement Close Day</label>
        <input type="number" name="statement_close_day" min="1" max="31" class="form-control" value="{{ old('statement_close_day', $creditCard->statement_close_day ?? '') }}">
    </div>
</div>
<div class="form-check mt-3">
    <input type="checkbox" name="autopay_enabled" class="form-check-input" id="autopay_enabled" value="1" @checked(old('autopay_enabled', $creditCard->autopay_enabled ?? false))>
    <label for="autopay_enabled" class="form-check-label">Autopay enabled</label>
</div>
<div class="mb-3 mt-2">
    <label class="form-label">Autopay Pay From</label>
    <select name="autopay_pay_from_account_id" class="form-select">
        <option value="">Select account</option>
        @foreach ($payFromAccounts as $account)
            <option value="{{ $account->id }}" @selected(old('autopay_pay_from_account_id', $creditCard->autopay_pay_from_account_id ?? '') == $account->id)>{{ $account->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $creditCard->notes ?? '') }}</textarea>
</div>
<button class="btn btn-primary" type="submit">Save</button>
<a href="{{ route('credit-cards.index') }}" class="btn btn-outline-secondary">Cancel</a>

@extends('layouts.app')

@section('title', 'Pay ' . $account->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Pay {{ $account->name }}</h1>
        <div class="text-muted">Previous due {{ $previousCycle['due_date']->format('M j, Y') }}</div>
    </div>
    <a href="{{ route('credit-cards.show', $account) }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Previous Statement Balance</div>
                <div class="display-6">${{ number_format($previousStatementBalance, 2) }}</div>
                <div class="text-muted small">Period {{ $previousCycle['period_start']->format('M j') }} - {{ $previousCycle['period_end']->format('M j') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Current Balance</div>
                <div class="display-6">${{ number_format($currentBalance, 2) }}</div>
                <div class="text-muted small">Current cycle closes {{ $currentCycle['period_end']->format('M j') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('credit-cards.pay', $account) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="funding_account_id">Funding Account</label>
                    <select name="funding_account_id" id="funding_account_id" class="form-select @error('funding_account_id') is-invalid @enderror" required>
                        <option value="">Select account</option>
                        @foreach ($fundingAccounts as $fundingAccount)
                            <option value="{{ $fundingAccount->id }}" @selected(old('funding_account_id', $creditCard->default_funding_account_id) == $fundingAccount->id)>{{ $fundingAccount->name }}</option>
                        @endforeach
                    </select>
                    @error('funding_account_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="amount">Amount</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $previousStatementBalance > 0 ? $previousStatementBalance : $currentBalance) }}" required>
                    @error('amount')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="payment_date">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', now()->toDateString()) }}" required>
                    @error('payment_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label class="form-label" for="apply_to_cycle">Apply To</label>
                    <select name="apply_to_cycle" id="apply_to_cycle" class="form-select @error('apply_to_cycle') is-invalid @enderror">
                        <option value="previous" @selected(old('apply_to_cycle', 'previous') === 'previous')>Previous Statement (Due {{ $previousCycle['due_date']->format('M j') }})</option>
                        <option value="current" @selected(old('apply_to_cycle') === 'current')>Current Cycle (Closes {{ $currentCycle['period_end']->format('M j') }})</option>
                    </select>
                    @error('apply_to_cycle')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="memo">Memo</label>
                    <input type="text" name="memo" id="memo" class="form-control @error('memo') is-invalid @enderror" value="{{ old('memo') }}" placeholder="Optional note">
                    @error('memo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Record Payment</button>
                <a href="{{ route('credit-cards.show', $account) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

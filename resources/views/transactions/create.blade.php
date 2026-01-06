@extends('layouts.app')

@section('title', 'Add Transaction')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Add Transaction</h1>
        <p class="text-muted mb-0">Record a manual ledger entry.</p>
    </div>
    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Back to Transactions</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('transactions.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label" for="date">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ old('date', $today->toDateString()) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="type">Type</label>
                <select name="type" id="type" class="form-select" required>
                    <option value="income" @selected(old('type') === 'income')>Income</option>
                    <option value="expense" @selected(old('type') === 'expense')>Expense</option>
                    <option value="transfer" @selected(old('type') === 'transfer')>Transfer</option>
                    <option value="credit_charge" @selected(old('type') === 'credit_charge')>Credit Charge</option>
                    <option value="credit_payment" @selected(old('type') === 'credit_payment')>Credit Card Payment</option>
                    <option value="adjustment" @selected(old('type') === 'adjustment')>Adjustment</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="amount">Amount</label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="currency">Currency</label>
                <input type="text" class="form-control" id="currency" name="currency" value="{{ old('currency', 'USD') }}" maxlength="10">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="from_account_id">From Account</label>
                <select name="from_account_id" id="from_account_id" class="form-select">
                    <option value="">—</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('from_account_id') == $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">Required for expenses, transfers, and credit payments.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="to_account_id">To Account</label>
                <select name="to_account_id" id="to_account_id" class="form-select">
                    <option value="">—</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('to_account_id') == $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">Required for income, transfers, and credit payments.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="account_id">Single Account</label>
                <select name="account_id" id="account_id" class="form-select">
                    <option value="">—</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">Use for credit charges or adjustments.</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="memo">Memo</label>
                <input type="text" class="form-control" id="memo" name="memo" value="{{ old('memo') }}" maxlength="255">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Save Transaction</button>
            </div>
        </form>
    </div>
</div>
@endsection

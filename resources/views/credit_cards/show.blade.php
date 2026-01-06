@extends('layouts.app')

@section('title', $account->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">{{ $account->name }}</h1>
        <div class="text-muted">{{ $creditCard->issuer_name }} &middot; ****{{ $creditCard->last4 }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('credit-cards.payForm', $account) }}" class="btn btn-primary">Pay Statement</a>
        <a href="{{ route('credit-cards.edit', $creditCard) }}" class="btn btn-outline-secondary">Edit</a>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Current Balance</div>
                <div class="display-6">${{ number_format($currentBalance, 2) }}</div>
                <div class="text-muted small">As of {{ now()->format('M j, Y') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Current Cycle</div>
                <div class="fw-semibold">{{ $currentCycle['period_start']->format('M j') }} - {{ $currentCycle['period_end']->format('M j') }}</div>
                <div class="small text-muted">Due {{ $currentCycle['due_date']->format('M j, Y') }}</div>
                <div class="mt-2">Est. Statement: ${{ number_format($currentStatementBalance, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Previous Cycle</div>
                <div class="fw-semibold">{{ $previousCycle['period_start']->format('M j') }} - {{ $previousCycle['period_end']->format('M j') }}</div>
                <div class="small text-muted">Due {{ $previousCycle['due_date']->format('M j, Y') }}</div>
                <div class="mt-2">Statement: ${{ number_format($previousStatementBalance, 2) }}</div>
                <form action="{{ route('credit-cards.createStatementPayment', $account) }}" method="POST" class="mt-2">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary" type="submit">Create Scheduled Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h5 class="card-title">Card Details</h5>
        <dl class="row mb-0">
            <dt class="col-sm-4">Close Day</dt>
            <dd class="col-sm-8">{{ $creditCard->statement_close_day ?? 'n/a' }}</dd>
            <dt class="col-sm-4">Payment Due Day</dt>
            <dd class="col-sm-8">{{ $creditCard->payment_due_day ?? $creditCard->due_day }}</dd>
            <dt class="col-sm-4">Default Funding</dt>
            <dd class="col-sm-8">{{ $creditCard->defaultFundingAccount?->name ?? 'Not set' }}</dd>
            <dt class="col-sm-4">Autopay</dt>
            <dd class="col-sm-8">
                <span class="badge {{ $creditCard->autopay_enabled ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">{{ $creditCard->autopay_enabled ? 'Enabled' : 'Disabled' }}</span>
                @if($creditCard->autopay_mode)
                    <span class="ms-2 text-muted">Mode: {{ ucfirst($creditCard->autopay_mode) }}</span>
                @endif
            </dd>
            <dt class="col-sm-4">Notes</dt>
            <dd class="col-sm-8">{{ $creditCard->notes ?? '—' }}</dd>
        </dl>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Transactions</h5>
            <form method="GET" class="d-flex align-items-center">
                <label class="me-2 mb-0" for="month">Month</label>
                <input type="month" id="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control" onchange="this.form.submit()">
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th>Memo</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->date->format('Y-m-d') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</td>
                        <td class="text-end">${{ number_format($transaction->amount, 2) }}</td>
                        <td>{{ $transaction->memo }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No transactions.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

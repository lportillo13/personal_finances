@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Transactions</h1>
        <p class="text-muted mb-0">Review your ledger entries.</p>
    </div>
    <a href="{{ route('transactions.create') }}" class="btn btn-primary">Add Transaction</a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row row-cols-lg-auto g-2 align-items-end">
            <div class="col-12 col-lg-3">
                <label class="form-label mb-1" for="month">Month</label>
                <input type="month" id="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control">
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label mb-1" for="account_id">Account</label>
                <select name="account_id" id="account_id" class="form-select">
                    <option value="">All accounts</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected($selectedAccountId == $account->id)>{{ $account->name }} ({{ str_replace('_', ' ', $account->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-auto">
                <button class="btn btn-outline-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if ($transactions->isEmpty())
            <p class="text-muted text-center my-4 mb-0">No transactions for this period.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 14%">Date</th>
                            <th style="width: 10%">Type</th>
                            <th style="width: 14%" class="text-end">Amount</th>
                            <th style="width: 24%">Accounts</th>
                            <th>Memo</th>
                            <th style="width: 16%">Linked Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            @php
                                $typeClass = match($transaction->type) {
                                    'income' => 'success',
                                    'expense' => 'danger',
                                    'transfer', 'credit_payment' => 'primary',
                                    'credit_charge' => 'warning text-dark',
                                    default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td>{{ optional($transaction->date)->toFormattedDateString() }}</td>
                                <td><span class="badge bg-{{ $typeClass }} text-uppercase">{{ str_replace('_', ' ', $transaction->type) }}</span></td>
                                <td class="text-end fw-semibold">${{ number_format($transaction->amount, 2) }}</td>
                                <td>
                                    @if ($transaction->type === 'income')
                                        <div>To: {{ $transaction->toAccount?->name ?? '—' }}</div>
                                    @elseif ($transaction->type === 'expense')
                                        <div>From: {{ $transaction->fromAccount?->name ?? '—' }}</div>
                                    @elseif ($transaction->type === 'transfer' || $transaction->type === 'credit_payment')
                                        <div>From: {{ $transaction->fromAccount?->name ?? '—' }}</div>
                                        <div>To: {{ $transaction->toAccount?->name ?? '—' }}</div>
                                    @elseif ($transaction->type === 'credit_charge' || $transaction->type === 'adjustment')
                                        <div>Account: {{ $transaction->account?->name ?? '—' }}</div>
                                    @endif
                                </td>
                                <td>{{ $transaction->memo ?? '—' }}</td>
                                <td>
                                    @if ($transaction->scheduledItem)
                                        <div>{{ $transaction->scheduledItem->recurringRule?->name ?? 'Scheduled Item' }}</div>
                                        <div class="text-muted small">{{ $transaction->scheduledItem->date?->toFormattedDateString() }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

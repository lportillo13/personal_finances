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
            <div class="col-12 col-lg-3">
                <label class="form-label mb-1" for="reconciled">Reconciled</label>
                <select name="reconciled" id="reconciled" class="form-select">
                    <option value="">All</option>
                    <option value="yes" @selected($reconciledFilter === 'yes')>Reconciled</option>
                    <option value="no" @selected($reconciledFilter === 'no')>Unreconciled</option>
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
            <form method="POST" action="{{ route('transactions.reconcile') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <input type="hidden" name="account_id" value="{{ $selectedAccountId }}">
                <input type="hidden" name="reconciled" value="{{ $reconciledFilter }}">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 6%"><input type="checkbox" class="form-check-input" id="select_all_transactions"></th>
                                <th style="width: 12%">Date</th>
                                <th style="width: 10%">Type</th>
                                <th style="width: 14%" class="text-end">Amount</th>
                                <th style="width: 22%">Accounts</th>
                                <th>Memo</th>
                                <th style="width: 16%">Linked Item</th>
                                <th style="width: 12%">Actions</th>
                                <th style="width: 16%">Status</th>
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
                                    $isDuplicate = $transaction->hash && in_array($transaction->hash, $duplicateHashes, true);
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input transaction-check" name="transaction_ids[]" value="{{ $transaction->id }}">
                                    </td>
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
                                    <td>
                                        <div>{{ $transaction->memo ?? '—' }}</div>
                                        <div class="text-muted small">{{ $transaction->source ? strtoupper($transaction->source) : 'MANUAL' }}</div>
                                    </td>
                                    <td>
                                        @if ($transaction->scheduledItem)
                                            <div>{{ $transaction->scheduledItem->recurringRule?->name ?? 'Scheduled Item' }}</div>
                                            <div class="text-muted small">{{ $transaction->scheduledItem->date?->toFormattedDateString() }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this transaction?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                                            <input type="hidden" name="account_id" value="{{ $selectedAccountId }}">
                                            <input type="hidden" name="reconciled" value="{{ $reconciledFilter }}">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                    <td>
                                        @if ($transaction->is_reconciled)
                                            <span class="badge bg-success">Reconciled</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                        @if ($isDuplicate)
                                            <span class="badge bg-warning text-dark">Duplicate</span>
                                        @endif
                                        @if ($transaction->imported_at)
                                            <span class="badge bg-info text-dark">Imported</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Manual</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex gap-2 flex-wrap align-items-center">
                    <button class="btn btn-success" name="action" value="reconcile" type="submit">Mark Reconciled</button>
                    <button class="btn btn-outline-secondary" name="action" value="unreconcile" type="submit">Unreconcile</button>
                    <span class="text-muted small">Select rows to mark them reconciled or unreconciled.</span>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const selectAllTransactions = document.getElementById('select_all_transactions');
    const transactionChecks = document.querySelectorAll('.transaction-check');
    if (selectAllTransactions && transactionChecks.length) {
        selectAllTransactions.addEventListener('change', (event) => {
            transactionChecks.forEach(cb => cb.checked = event.target.checked);
        });
    }
</script>
@endpush

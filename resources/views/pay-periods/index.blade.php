@extends('layouts.app')

@section('title', 'Pay Periods')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Pay Periods</h1>
        <p class="text-muted mb-0">{{ $start->toFormattedDateString() }} to {{ $end->toFormattedDateString() }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" class="row row-cols-lg-auto g-2 align-items-end">
            <div class="col">
                <label class="form-label mb-1" for="from">From</label>
                <input type="date" class="form-control" id="from" name="from" value="{{ $start->toDateString() }}">
            </div>
            <div class="col">
                <label class="form-label mb-1" for="to">To</label>
                <input type="date" class="form-control" id="to" name="to" value="{{ $end->toDateString() }}">
            </div>
            <div class="col">
                <label class="form-label mb-1" for="savings">Savings (per paycheck)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="savings" name="savings" value="{{ $savingsPerPaycheck }}">
            </div>
            <div class="col">
                <button class="btn btn-outline-primary">Update</button>
            </div>
        </form>
        <form method="POST" action="{{ route('pay-periods.allocate') }}">
            @csrf
            <input type="hidden" name="from" value="{{ $start->toDateString() }}">
            <input type="hidden" name="to" value="{{ $end->toDateString() }}">
            <button class="btn btn-primary">Allocate by Paychecks</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Total Income</h5>
                <p class="display-6 text-success mb-0">${{ number_format($incomes->sum('amount'), 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Allocated Expenses</h5>
                <p class="display-6 text-danger mb-0">${{ number_format($incomes->flatMap->allocationsAsIncome->sum('allocated_amount'), 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Savings Reserved</h5>
                <p class="display-6 text-primary mb-0">${{ number_format($savingsPerPaycheck * $incomes->count(), 2) }}</p>
            </div>
        </div>
    </div>
</div>

@forelse ($incomes as $income)
    @php
        $allocatedTotal = $income->allocationsAsIncome->sum('allocated_amount');
        $freeAmount = $income->amount - $allocatedTotal - $savingsPerPaycheck;
        $badgeColor = $income->category?->color ?? '#198754';
    @endphp
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center">
                <span class="badge me-2" style="background-color: {{ $badgeColor }}">Income</span>
                <div>
                    <div class="fw-semibold">{{ $income->recurringRule?->name ?? 'Paycheck' }}</div>
                    <div class="text-muted small">{{ $income->date->toFormattedDateString() }}</div>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-success">${{ number_format($income->amount, 2) }}</div>
                <div class="small text-muted">Savings: ${{ number_format($savingsPerPaycheck, 2) }}</div>
                <div class="small">Allocated: <span class="text-danger">${{ number_format($allocatedTotal, 2) }}</span></div>
                <div class="small fw-semibold {{ $freeAmount >= 0 ? 'text-success' : 'text-danger' }}">Free: ${{ number_format($freeAmount, 2) }}</div>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($income->allocationsAsIncome->isEmpty())
                <p class="text-muted text-center my-3">No expenses allocated yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 18%">Due</th>
                                <th>Name</th>
                                <th style="width: 20%">Account</th>
                                <th style="width: 18%" class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($income->allocationsAsIncome as $allocation)
                                <tr>
                                    <td>{{ optional($allocation->expenseItem->date)->toFormattedDateString() }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $allocation->expenseItem->recurringRule?->name ?? 'Expense' }}</div>
                                        @if ($allocation->expenseItem->category)
                                            <span class="badge rounded-pill" style="background-color: {{ $allocation->expenseItem->category->color ?? '#6c757d' }}">{{ $allocation->expenseItem->category->name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $allocation->expenseItem->account?->name ?? '—' }}</td>
                                    <td class="text-end text-danger">${{ number_format($allocation->allocated_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info">No paychecks found in this range.</div>
@endforelse

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Unallocated Expenses</h5>
            <small class="text-muted">These items could not be paired to a paycheck.</small>
        </div>
        <span class="badge bg-danger-subtle text-danger">{{ $unallocatedExpenses->count() }} items</span>
    </div>
    <div class="card-body p-0">
        @if ($unallocatedExpenses->isEmpty())
            <p class="text-muted text-center my-3 mb-0">All expenses are allocated.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 18%">Due</th>
                            <th>Name</th>
                            <th style="width: 20%">Account</th>
                            <th style="width: 18%" class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unallocatedExpenses as $expense)
                            <tr>
                                <td>{{ $expense->date->toFormattedDateString() }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $expense->recurringRule?->name ?? 'Expense' }}</div>
                                    @if ($expense->category)
                                        <span class="badge rounded-pill" style="background-color: {{ $expense->category->color ?? '#6c757d' }}">{{ $expense->category->name }}</span>
                                    @endif
                                </td>
                                <td>{{ $expense->account?->name ?? '—' }}</td>
                                <td class="text-end text-danger">${{ number_format($expense->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

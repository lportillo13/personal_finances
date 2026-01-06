@extends('layouts.app')

@section('title', 'Pay Periods')

@section('content')
@php
    $incomeTotal = $incomes->sum('amount');
    $allocatedTotal = $incomes->flatMap->allocationsAsIncome->sum('allocated_amount');
    $freeTotal = $incomeTotal - $allocatedTotal - $savingsTotal;
@endphp
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Pay Periods</h1>
        <p class="text-muted mb-0">{{ $start->toFormattedDateString() }} to {{ $end->toFormattedDateString() }}</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
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
                <button class="btn btn-outline-primary">Update</button>
            </div>
        </form>
        <form method="POST" action="{{ route('pay-periods.allocate') }}" class="d-flex align-items-center gap-2">
            @csrf
            <input type="hidden" name="from" value="{{ $start->toDateString() }}">
            <input type="hidden" name="to" value="{{ $end->toDateString() }}">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="force_reallocate" name="force_reallocate">
                <label class="form-check-label small" for="force_reallocate">Force re-auto-allocate</label>
            </div>
            <button class="btn btn-primary">Allocate by Paychecks</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Total Income</h5>
                <p class="display-6 text-success mb-0">${{ number_format($incomeTotal, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Allocated Expenses</h5>
                <p class="display-6 text-danger mb-0">${{ number_format($allocatedTotal, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Savings Reserved</h5>
                <p class="display-6 text-primary mb-1">${{ number_format($savingsTotal, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">Free After Savings</h5>
                <p class="display-6 {{ $freeTotal >= 0 ? 'text-success' : 'text-danger' }} mb-0">${{ number_format($freeTotal, 2) }}</p>
            </div>
        </div>
    </div>
</div>

@php
    $incomeOptions = $incomes->map(fn ($income) => [
        'id' => $income->id,
        'label' => $income->date->toFormattedDateString(),
    ]);
@endphp

@forelse ($incomes as $income)
    @php
        $allocated = $income->allocationsAsIncome->sum('allocated_amount');
        $savingsAmount = (float) ($income->savingsBucket->amount ?? 0);
        $freeAmount = $income->amount - $allocated - $savingsAmount;
        $badgeColor = $income->category?->color ?? '#198754';
    @endphp
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <span class="badge me-2" style="background-color: {{ $badgeColor }}">Income</span>
                <div>
                    <div class="fw-semibold">{{ $income->recurringRule?->name ?? 'Paycheck' }}</div>
                    <div class="text-muted small">{{ $income->date->toFormattedDateString() }}</div>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-success">${{ number_format($income->amount, 2) }}</div>
                <div class="small text-primary">Savings: ${{ number_format($savingsAmount, 2) }}</div>
                <div class="small">Allocated: <span class="text-danger">${{ number_format($allocated, 2) }}</span></div>
                <div class="small fw-semibold {{ $freeAmount >= 0 ? 'text-success' : 'text-danger' }}">Free: ${{ number_format($freeAmount, 2) }}</div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="mb-3">
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#savings-{{ $income->id }}">Edit Savings</button>
                <div class="collapse mt-2" id="savings-{{ $income->id }}">
                    <form method="POST" action="{{ route('pay-periods.savings', $income) }}" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="from" value="{{ $start->toDateString() }}">
                        <input type="hidden" name="to" value="{{ $end->toDateString() }}">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Amount</label>
                            <input type="number" step="0.01" min="0" name="amount" value="{{ $savingsAmount }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-1">Note</label>
                            <input type="text" name="note" value="{{ $income->savingsBucket?->note }}" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button class="btn btn-primary w-100">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($income->allocationsAsIncome->isEmpty())
                <p class="text-muted mb-0">No expenses allocated yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 14%">Due</th>
                                <th>Name</th>
                                <th style="width: 18%">Account</th>
                                <th style="width: 12%" class="text-end">Amount</th>
                                <th style="width: 30%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($income->allocationsAsIncome as $allocation)
                                @php
                                    $expense = $allocation->expenseItem;
                                    $remaining = max(0, (float) $expense->amount - $expense->allocationsAsExpense->sum('allocated_amount'));
                                @endphp
                                <tr>
                                    <td>{{ optional($expense->date)->toFormattedDateString() }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $expense->recurringRule?->name ?? 'Expense' }}</div>
                                        @if ($expense->category)
                                            <span class="badge rounded-pill" style="background-color: {{ $expense->category->color ?? '#6c757d' }}">{{ $expense->category->name }}</span>
                                        @endif
                                        @php
                                            $statusClass = match ($expense->status) {
                                                'paid' => 'bg-success',
                                                'skipped' => 'bg-warning text-dark',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            <span class="badge {{ $statusClass }} text-uppercase">{{ ucfirst($expense->status ?? 'pending') }}</span>
                                            @if ($expense->paid_at)
                                                <small class="text-muted">Paid {{ $expense->paid_at->toFormattedDateString() }}</small>
                                            @endif
                                            @if (($expense->status ?? 'pending') !== 'paid' && optional($expense->date)->lte(now()))
                                                <form method="POST" action="{{ route('scheduled-items.markPaid', $expense) }}" class="d-inline-flex gap-1 align-items-center">
                                                    @csrf
                                                    <input type="hidden" name="actual_amount" value="{{ $expense->actual_amount ?? $expense->amount }}">
                                                    <button class="btn btn-sm btn-outline-success">Mark Paid</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $expense->account?->name ?? '—' }}</td>
                                    <td class="text-end text-danger">${{ number_format($allocation->allocated_amount, 2) }}</td>
                                    <td>
                                        <div class="row g-2 align-items-center">
                                            <div class="col-12">
                                                <form class="row row-cols-lg-auto g-2 align-items-center" method="POST" action="{{ route('allocations.reassign') }}">
                                                    @csrf
                                                    <input type="hidden" name="allocation_id" value="{{ $allocation->id }}">
                                                    <div class="col">
                                                        <select name="target_income_scheduled_item_id" class="form-select form-select-sm">
                                                            @foreach ($incomeOptions as $option)
                                                                @if ($option['id'] !== $income->id)
                                                                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ $allocation->allocated_amount }}" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col">
                                                        <button class="btn btn-sm btn-outline-secondary">Reassign</button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-12">
                                                <form class="row row-cols-lg-auto g-2 align-items-center" method="POST" action="{{ route('allocations.split') }}">
                                                    @csrf
                                                    <input type="hidden" name="expense_scheduled_item_id" value="{{ $expense->id }}">
                                                    <div class="col">
                                                        <select name="income_scheduled_item_id" class="form-select form-select-sm">
                                                            @foreach ($incomeOptions as $option)
                                                                @if ($option['id'] !== $income->id)
                                                                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ $remaining > 0 ? $remaining : $allocation->allocated_amount }}" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col">
                                                        <button class="btn btn-sm btn-outline-primary">Split</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @if ($remaining > 0)
                                            <div class="small text-warning mt-1">Remaining: ${{ number_format($remaining, 2) }}</div>
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
@empty
    <div class="alert alert-info">No paychecks found in this range.</div>
@endforelse

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Unallocated Remainders</h5>
            <small class="text-muted">Expenses with remaining balance not yet assigned.</small>
        </div>
        <span class="badge bg-danger-subtle text-danger">{{ $unallocatedExpenses->count() }} items</span>
    </div>
    <div class="card-body p-0">
        @if ($unallocatedExpenses->isEmpty())
            <p class="text-muted text-center my-3 mb-0">All expenses are fully allocated.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 18%">Due</th>
                            <th>Name</th>
                            <th style="width: 18%">Account</th>
                            <th style="width: 16%" class="text-end">Remaining</th>
                            <th style="width: 30%">Allocate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unallocatedExpenses as $expense)
                            @php
                                $remaining = max(0, (float) $expense->amount - $expense->allocationsAsExpense->sum('allocated_amount'));
                            @endphp
                            <tr>
                                <td>{{ $expense->date->toFormattedDateString() }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $expense->recurringRule?->name ?? 'Expense' }}</div>
                                    @if ($expense->category)
                                        <span class="badge rounded-pill" style="background-color: {{ $expense->category->color ?? '#6c757d' }}">{{ $expense->category->name }}</span>
                                    @endif
                                    @php
                                        $statusClass = match ($expense->status) {
                                            'paid' => 'bg-success',
                                            'skipped' => 'bg-warning text-dark',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                        <span class="badge {{ $statusClass }} text-uppercase">{{ ucfirst($expense->status ?? 'pending') }}</span>
                                        @if ($expense->paid_at)
                                            <small class="text-muted">Paid {{ $expense->paid_at->toFormattedDateString() }}</small>
                                        @endif
                                        @if (($expense->status ?? 'pending') !== 'paid' && optional($expense->date)->lte(now()))
                                            <form method="POST" action="{{ route('scheduled-items.markPaid', $expense) }}" class="d-inline-flex gap-1 align-items-center">
                                                @csrf
                                                <input type="hidden" name="actual_amount" value="{{ $expense->actual_amount ?? $expense->amount }}">
                                                <button class="btn btn-sm btn-outline-success">Mark Paid</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $expense->account?->name ?? '—' }}</td>
                                <td class="text-end text-danger">${{ number_format($remaining, 2) }}</td>
                                <td>
                                    <form class="row row-cols-lg-auto g-2 align-items-center" method="POST" action="{{ route('allocations.split') }}">
                                        @csrf
                                        <input type="hidden" name="expense_scheduled_item_id" value="{{ $expense->id }}">
                                        <div class="col">
                                            <select name="income_scheduled_item_id" class="form-select form-select-sm">
                                                @foreach ($incomeOptions as $option)
                                                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col">
                                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ $remaining }}" class="form-control form-control-sm">
                                        </div>
                                        <div class="col">
                                            <button class="btn btn-sm btn-outline-primary">Allocate</button>
                                        </div>
                                    </form>
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

@extends('layouts.app')

@section('title', 'Day View')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-0">{{ $date->toFormattedDateString() }}</h1>
        <p class="text-muted mb-0">Scheduled items for this day</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('calendar.index', ['month' => $date->format('Y-m')]) }}">Back to Calendar</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if ($items->isEmpty())
            <p class="text-muted text-center my-4">No items scheduled.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th class="text-end" style="width: 15%">Amount</th>
                            <th style="width: 26%">Payment</th>
                            <th style="width: 26%">Allocate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $allocated = $item->allocationsAsExpense->sum('allocated_amount');
                                $remaining = max(0, (float) $item->amount - $allocated);
                                $badgeClass = 'bg-secondary';
                                if ($item->kind === 'income' || $item->category?->kind === 'income') {
                                    $badgeClass = 'bg-success';
                                } elseif ($item->kind === 'expense' || $item->category?->kind === 'expense') {
                                    $badgeClass = 'bg-danger';
                                } elseif ($item->kind === 'transfer') {
                                    $badgeClass = 'bg-primary';
                                }
                            @endphp
                            <tr>
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($item->kind) }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $item->recurringRule?->name ?? $item->notes ?? $item->note ?? 'Scheduled Item' }}</div>
                                    @if ($item->category)
                                        <span class="badge rounded-pill" style="background-color: {{ $item->category->color ?? '#6c757d' }}">{{ $item->category->name }}</span>
                                    @endif
                                </td>
                                <td class="text-end">${{ number_format($item->amount, 2) }}</td>
                                <td>
                                    @php
                                        $paymentBadge = match ($item->status) {
                                            'paid' => 'bg-success',
                                            'skipped' => 'bg-warning text-dark',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge {{ $paymentBadge }} text-uppercase">{{ ucfirst($item->status) }}</span>
                                        @if ($item->paid_at)
                                            <small class="text-muted">{{ $item->paid_at->format('M j, g:ia') }}</small>
                                        @endif
                                    </div>
                                    @if ($item->actual_amount && (float) $item->actual_amount !== (float) $item->amount)
                                        <div class="small text-muted mb-1">Actual: ${{ number_format($item->actual_amount, 2) }}</div>
                                    @endif
                                    <form class="row g-2 align-items-center mb-2" method="POST" action="{{ route('scheduled-items.markPaid', $item) }}">
                                        @csrf
                                        <div class="col-auto">
                                            <input type="number" step="0.01" min="0" name="actual_amount" value="{{ old('actual_amount', $item->actual_amount ?? $item->amount) }}" class="form-control form-control-sm" style="width: 120px;" placeholder="Amount">
                                        </div>
                                        <div class="col">
                                            <input type="text" name="note" value="{{ old('note', $item->note) }}" class="form-control form-control-sm" placeholder="Note (optional)">
                                        </div>
                                        <div class="col-auto">
                                            <button class="btn btn-sm btn-success">Mark Paid</button>
                                        </div>
                                    </form>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('scheduled-items.markSkipped', $item) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" type="submit">Mark Skipped</button>
                                        </form>
                                        <form method="POST" action="{{ route('scheduled-items.markPending', $item) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Pending</button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    @if ($item->kind === 'expense' && $remaining > 0)
                                        <form class="row row-cols-lg-auto g-2 align-items-center" method="POST" action="{{ route('allocations.split') }}">
                                            @csrf
                                            <input type="hidden" name="expense_scheduled_item_id" value="{{ $item->id }}">
                                            <div class="col">
                                                <select name="income_scheduled_item_id" class="form-select form-select-sm">
                                                    @if ($suggestedIncome)
                                                        <option value="{{ $suggestedIncome->id }}">Nearest: {{ $suggestedIncome->date->toFormattedDateString() }}</option>
                                                    @endif
                                                    @foreach ($incomeOptions as $income)
                                                        <option value="{{ $income->id }}">{{ $income->date->toFormattedDateString() }}</option>
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

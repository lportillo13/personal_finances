@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Upcoming Schedule</h1>
        <p class="text-muted mb-0">{{ $start->toFormattedDateString() }} to {{ $end->toFormattedDateString() }}</p>
    </div>
    <form method="POST" action="{{ route('schedule.generate') }}">
        @csrf
        <button class="btn btn-primary">Generate Schedule (Next 90 Days)</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Income (next 30 days)</h5>
                <p class="display-6 text-success">${{ number_format($incomeTotal, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Expenses (next 30 days)</h5>
                <p class="display-6 text-danger">${{ number_format($expenseTotal, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Net</h5>
                <p class="display-6 {{ $netTotal >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($netTotal, 2) }}</p>
            </div>
        </div>
    </div>
</div>

@if ($groupedItems->isEmpty())
    <div class="alert alert-info">No scheduled items in this range. Try generating the schedule.</div>
@else
    @foreach ($groupedItems as $date => $items)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">
                <strong>{{ \Carbon\Carbon::parse($date)->toFormattedDateString() }}</strong>
            </div>
            <ul class="list-group list-group-flush">
                @foreach ($items as $item)
                    @php
                        $badgeColor = $item->category?->color;
                        if (! $badgeColor) {
                            $badgeColor = match ($item->kind) {
                                'income' => '#198754',
                                'transfer' => '#0d6efd',
                                default => '#dc3545',
                            };
                        }
                    @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge me-2" style="background-color: {{ $badgeColor }}">{{ ucfirst($item->kind) }}</span>
                                <div class="fw-semibold">{{ $item->recurringRule?->name ?? 'Scheduled Item' }}</div>
                            </div>
                            <div class="text-muted small">
                                @if ($item->account)
                                    <span class="me-2">Account: {{ $item->account->name }}</span>
                                @endif
                                @if ($item->category)
                                    <span class="me-2">Category: {{ $item->category->name }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="fw-bold {{ $item->kind === 'income' ? 'text-success' : ($item->kind === 'expense' ? 'text-danger' : 'text-primary') }}">${{ number_format($item->amount, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
@endif
@endsection

@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Upcoming Schedule</h1>
        <p class="text-muted mb-0">{{ $start->toFormattedDateString() }} to {{ $end->toFormattedDateString() }}</p>
    </div>
    <form method="POST" action="{{ route('schedule.generate') }}">
        @csrf
        <button class="btn btn-primary">Generate schedule</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Income (next 30 days)</h5>
                <p class="display-6 text-success">${{ number_format($incomeTotal, 2) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Expenses (next 30 days)</h5>
                <p class="display-6 text-danger">${{ number_format($expenseTotal, 2) }}</p>
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
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold text-capitalize">{{ $item->kind }}</div>
                            @if ($item->category)
                                <div class="small text-muted">{{ $item->category->name }}</div>
                            @endif
                        </div>
                        <span class="fw-bold {{ $item->kind === 'income' ? 'text-success' : 'text-danger' }}">${{ number_format($item->amount, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
@endif
@endsection

@extends('layouts.app')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Upcoming totals ({{ $start->format('M j') }} - {{ $end->format('M j') }})</h2>
                <div class="d-flex justify-content-between mt-3">
                    <div>
                        <div class="text-muted">Income</div>
                        <div class="fs-4 text-success">{{ number_format($incomeTotal, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-muted">Expenses</div>
                        <div class="fs-4 text-danger">{{ number_format($expenseTotal, 2) }}</div>
                    </div>
                </div>
                <p class="text-muted mt-3 mb-0">Totals are based on scheduled items in the next 14 days.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5">Quick links</h2>
                <div class="list-group mt-3">
                    <a href="{{ route('accounts.index') }}" class="list-group-item list-group-item-action">Manage accounts</a>
                    <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action">Manage categories</a>
                    <a href="{{ route('recurring-rules.index') }}" class="list-group-item list-group-item-action">Manage recurring rules</a>
                    <form method="POST" action="{{ route('scheduled-items.generate') }}" class="list-group-item">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Generate schedule (90 days)</span>
                            <button class="btn btn-outline-secondary btn-sm" type="submit">Run</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5">Upcoming 14 days</h2>
        @forelse ($groupedItems as $date => $items)
            <div class="mt-3">
                <div class="fw-semibold">{{ \Carbon\Carbon::parse($date)->format('l, F j') }}</div>
                <ul class="list-group mt-2">
                    @foreach ($items as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div>{{ $item->category->name ?? ucfirst($item->direction) }}</div>
                                <div class="text-muted small">{{ $item->account->name ?? 'Unassigned account' }}</div>
                            </div>
                            <span class="fw-semibold {{ $item->direction === 'income' ? 'text-success' : 'text-danger' }}">{{ number_format($item->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-muted mb-0">No scheduled items in the next two weeks.</p>
        @endforelse
    </div>
</div>
@endsection

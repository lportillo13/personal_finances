@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
@php
    $prevMonth = $start->copy()->subMonth();
    $nextMonth = $start->copy()->addMonth();
@endphp
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-0">{{ $start->format('F Y') }}</h1>
        <p class="text-muted mb-0">Scheduled income, expenses, and transfers</p>
    </div>
    <div class="btn-group" role="group">
        <a class="btn btn-outline-primary" href="{{ route('calendar.index', ['month' => $prevMonth->format('Y-m')]) }}">&laquo; {{ $prevMonth->format('M Y') }}</a>
        <a class="btn btn-outline-primary" href="{{ route('calendar.index', ['month' => $nextMonth->format('Y-m')]) }}">{{ $nextMonth->format('M Y') }} &raquo;</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-light">
            <tr>
                <th class="text-center">Sun</th>
                <th class="text-center">Mon</th>
                <th class="text-center">Tue</th>
                <th class="text-center">Wed</th>
                <th class="text-center">Thu</th>
                <th class="text-center">Fri</th>
                <th class="text-center">Sat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($weeks as $week)
                <tr>
                    @foreach ($week as $day)
                        @php
                            $dateKey = $day->toDateString();
                            $dayItems = $items->get($dateKey, collect());
                            $incomeTotal = $dayItems->where('kind', 'income')->sum('amount');
                            $expenseTotal = $dayItems->where('kind', 'expense')->sum('amount');
                            $fundingExpenseTotal = $fundingAccount
                                ? $dayItems->filter(fn ($item) => ($item->kind === 'expense' || $item->category?->kind === 'expense')
                                    && $item->account_id === $fundingAccount->id)->sum('amount')
                                : 0;
                            $fundingBalance = $fundingAccount ? ($fundingBalances[$dateKey] ?? null) : null;
                            $isToday = $day->isToday();
                        @endphp
                        <td class="align-top" style="min-width: 180px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="{{ $isToday ? 'text-primary' : '' }}">{{ $day->format('j') }}</strong>
                                <a href="{{ route('calendar.day', ['date' => $dateKey]) }}" class="small">View</a>
                            </div>
                            @if ($isToday)
                                <div class="badge bg-primary-subtle text-primary mb-1">Today</div>
                            @endif
                            @foreach ($dayItems->take(3) as $item)
                                @php
                                    $badgeClass = 'bg-secondary';
                                    if ($item->kind === 'income' || $item->category?->kind === 'income') {
                                        $badgeClass = 'bg-success';
                                    } elseif ($item->kind === 'expense' || $item->category?->kind === 'expense') {
                                        $badgeClass = 'bg-danger';
                                    } elseif ($item->kind === 'transfer') {
                                        $badgeClass = 'bg-primary';
                                    }
                                @endphp
                                <div class="small mb-1">
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($item->kind) }}</span>
                                    {{ $item->recurringRule?->name ?? $item->notes ?? 'Item' }}
                                </div>
                            @endforeach
                            @if ($dayItems->count() > 3)
                                <div class="small text-muted">+{{ $dayItems->count() - 3 }} more</div>
                            @endif
                            <div class="small text-success mt-2">Income: ${{ number_format($incomeTotal, 2) }}</div>
                            <div class="small text-danger">Expenses: ${{ number_format($expenseTotal, 2) }}</div>
                            @if ($fundingAccount)
                                @if ($fundingBalance !== null)
                                    <div class="small text-muted">Debit balance: ${{ number_format($fundingBalance, 2) }}</div>
                                @endif
                            @else
                                <div class="small text-muted">Set a funding account to see debit totals.</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

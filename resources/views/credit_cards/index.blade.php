@extends('layouts.app')

@section('title', 'Credit Cards')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Credit Cards</h1>
    <a href="{{ route('credit-cards.create') }}" class="btn btn-primary">Add Credit Card</a>
</div>

<div class="row g-3">
    @forelse ($cards as $card)
        @php($summary = $summaries[$card->id] ?? null)
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="card-title mb-0">{{ $card->account->name }}</h5>
                            <div class="text-muted small">{{ $card->issuer_name }} &middot; ****{{ $card->last4 }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('credit-cards.show', $card->account) }}" class="btn btn-sm btn-outline-secondary">Details</a>
                            <a href="{{ route('credit-cards.edit', $card) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </div>
                    </div>

                    @if (! is_null($card->current_amount))
                        <div class="mb-2">
                            <div class="small text-muted">Current Amount</div>
                            <div class="fw-semibold">${{ number_format($card->current_amount, 2) }}</div>
                        </div>
                    @endif

                    @if($summary)
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <div class="small text-muted">Current Balance</div>
                                <div class="fw-semibold">${{ number_format($summary['current_balance'], 2) }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Current Cycle</div>
                                <div>{{ $summary['current_cycle']['period_end']->format('M j') }} close</div>
                                <div class="text-muted small">Due {{ $summary['current_cycle']['due_date']->format('M j') }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Est. Statement</div>
                                <div>${{ number_format($summary['current_statement_balance'], 2) }}</div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="small text-muted">Last Statement</div>
                                <div>${{ number_format($summary['previous_statement_balance'], 2) }}</div>
                                <div class="text-muted small">Due {{ $summary['previous_cycle']['due_date']->format('M j') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Autopay</div>
                                <span class="badge {{ $card->autopay_enabled ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">{{ $card->autopay_enabled ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-muted">No cycle data.</div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <span class="small text-muted">Close day {{ $card->statement_close_day ?? 'n/a' }} &middot; Due day {{ $card->payment_due_day ?? $card->due_day }}</span>
                    <form action="{{ route('credit-cards.destroy', $card) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this credit card?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-secondary">No credit cards yet.</div>
        </div>
    @endforelse
</div>
@endsection

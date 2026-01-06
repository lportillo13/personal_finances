@extends('layouts.app')

@section('title', 'Credit Cards')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Credit Cards</h1>
    <a href="{{ route('credit-cards.create') }}" class="btn btn-primary">Add Credit Card</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Account</th>
                <th>Issuer</th>
                <th>Last4</th>
                <th>Due Day</th>
                <th>Autopay</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($cards as $card)
                <tr>
                    <td>{{ $card->account->name }}</td>
                    <td>{{ $card->issuer_name }}</td>
                    <td>{{ $card->last4 }}</td>
                    <td>{{ $card->due_day }}</td>
                    <td>{{ $card->autopay_enabled ? 'Yes' : 'No' }}</td>
                    <td class="text-end">
                        <a href="{{ route('credit-cards.edit', $card) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('credit-cards.destroy', $card) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this credit card?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No credit cards yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

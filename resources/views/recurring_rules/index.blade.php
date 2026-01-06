@extends('layouts.app')

@section('title', 'Recurring Rules')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Recurring Rules</h1>
    <a href="{{ route('recurring-rules.create') }}" class="btn btn-primary">Add Rule</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Name</th>
                <th>Kind</th>
                <th>Amount</th>
                <th>Frequency</th>
                <th>Next Run</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($rules as $rule)
                <tr>
                    <td>{{ $rule->name }}</td>
                    <td class="text-capitalize">{{ $rule->kind }}</td>
                    <td>${{ number_format($rule->amount, 2) }}</td>
                    <td class="text-capitalize">{{ $rule->frequency }}</td>
                    <td>{{ optional($rule->next_run_on)->toDateString() }}</td>
                    <td>{{ $rule->is_active ? 'Active' : 'Paused' }}</td>
                    <td class="text-end">
                        <a href="{{ route('recurring-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('recurring-rules.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this rule?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No recurring rules yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

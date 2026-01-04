@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Recurring rules</h1>
        <p class="text-muted mb-0">Income, expenses, and transfer templates.</p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('scheduled-items.generate') }}">
            @csrf
            <button class="btn btn-outline-secondary" type="submit">Generate schedule (next 90 days)</button>
        </form>
        <a href="{{ route('recurring-rules.create') }}" class="btn btn-primary">Add rule</a>
    </div>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Direction</th>
                    <th>Amount</th>
                    <th>Frequency</th>
                    <th>Next run</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rules as $rule)
                    <tr>
                        <td>{{ $rule->name }}</td>
                        <td class="text-capitalize">{{ $rule->direction }}</td>
                        <td>{{ number_format($rule->amount, 2) }} {{ $rule->currency }}</td>
                        <td>{{ ucfirst($rule->frequency) }}</td>
                        <td>{{ optional($rule->next_run_on)->format('Y-m-d') }}</td>
                        <td>{!! $rule->is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' !!}</td>
                        <td class="text-end">
                            <a href="{{ route('recurring-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('recurring-rules.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this rule?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No recurring rules yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

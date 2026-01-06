@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Accounts</h1>
    <a href="{{ route('accounts.create') }}" class="btn btn-primary">Add Account</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Currency</th>
                <th>Funding</th>
                <th>Active</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($accounts as $account)
                <tr>
                    <td>{{ $account->name }}</td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $account->type) }}</td>
                    <td>{{ $account->currency }}</td>
                    <td>{{ $account->is_funding ? 'Yes' : 'No' }}</td>
                    <td>{{ $account->is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-end">
                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No accounts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

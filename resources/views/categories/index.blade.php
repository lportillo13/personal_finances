@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Categories</h1>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">Add Category</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Name</th>
                <th>Kind</th>
                <th>Color</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td class="text-capitalize">{{ $category->kind }}</td>
                    <td><span class="badge" style="background: {{ $category->color }}">{{ $category->color }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No categories yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

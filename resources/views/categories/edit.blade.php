@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Edit category</h1>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('categories._form', ['category' => $category])
            <button class="btn btn-primary" type="submit">Update category</button>
        </form>
    </div>
</div>
@endsection

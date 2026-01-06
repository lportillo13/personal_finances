@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Add Category</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('categories.store') }}">
            @include('categories.form', ['category' => new \App\Models\Category()])
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Edit Category</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @method('PUT')
            @include('categories.form', ['category' => $category])
        </form>
    </div>
</div>
@endsection

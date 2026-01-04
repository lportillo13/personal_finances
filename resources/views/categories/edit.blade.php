@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Edit Category</h1>
<form method="POST" action="{{ route('categories.update', $category) }}" class="card card-body shadow-sm">
    @method('PUT')
    @include('categories.form', ['category' => $category])
</form>
@endsection

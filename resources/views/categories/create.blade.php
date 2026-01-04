@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Add Category</h1>
<form method="POST" action="{{ route('categories.store') }}" class="card card-body shadow-sm">
    @include('categories.form', ['category' => new \App\Models\Category()])
</form>
@endsection

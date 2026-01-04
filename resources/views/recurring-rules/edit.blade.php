@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Edit recurring rule</h1>
    <a href="{{ route('recurring-rules.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('recurring-rules.update', $rule) }}">
            @csrf
            @method('PUT')
            @include('recurring-rules._form', ['rule' => $rule])
            <button class="btn btn-primary mt-3" type="submit">Update rule</button>
        </form>
    </div>
</div>
@endsection

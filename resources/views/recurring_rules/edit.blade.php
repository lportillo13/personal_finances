@extends('layouts.app')

@section('title', 'Edit Recurring Rule')

@section('content')
<div class="row align-items-center mb-3">
    <div class="col">
        <h1 class="h4 mb-0">Edit Recurring Rule</h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('recurring-rules.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('recurring-rules.update', $rule) }}">
            @method('PUT')
            @include('recurring_rules._form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save</button>
                <a href="{{ route('recurring-rules.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

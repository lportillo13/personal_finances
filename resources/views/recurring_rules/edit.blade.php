@extends('layouts.app')

@section('title', 'Edit Recurring Rule')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Edit Recurring Rule</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('recurring-rules.update', $rule) }}">
            @method('PUT')
            @include('recurring_rules.form', ['rule' => $rule])
        </form>
    </div>
</div>
@endsection

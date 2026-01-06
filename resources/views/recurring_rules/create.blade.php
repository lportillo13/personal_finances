@extends('layouts.app')

@section('title', 'Add Recurring Rule')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Add Recurring Rule</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('recurring-rules.store') }}">
            @include('recurring_rules.form', ['rule' => new \App\Models\RecurringRule()])
        </form>
    </div>
</div>
@endsection

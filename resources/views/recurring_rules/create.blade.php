@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Add Recurring Rule</h1>
<form method="POST" action="{{ route('recurring-rules.store') }}" class="card card-body shadow-sm">
    @include('recurring_rules.form', ['rule' => new \App\Models\RecurringRule()])
</form>
@endsection

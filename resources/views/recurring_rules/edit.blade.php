@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Edit Recurring Rule</h1>
<form method="POST" action="{{ route('recurring-rules.update', $rule) }}" class="card card-body shadow-sm">
    @method('PUT')
    @include('recurring_rules.form', ['rule' => $rule])
</form>
@endsection

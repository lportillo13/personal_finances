@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Create recurring rule</h1>
    <a href="{{ route('recurring-rules.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('recurring-rules.store') }}">
            @csrf
            @include('recurring-rules._form', ['rule' => new \App\Models\RecurringRule()])
            <button class="btn btn-primary mt-3" type="submit">Save rule</button>
        </form>
    </div>
</div>
@endsection

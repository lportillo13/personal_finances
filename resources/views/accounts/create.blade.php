@extends('layouts.app')

@section('title', 'Add Account')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Add Account</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('accounts.store') }}">
            @include('accounts.form', ['account' => new \App\Models\Account()])
        </form>
    </div>
</div>
@endsection

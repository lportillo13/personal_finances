@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Add Account</h1>
<form method="POST" action="{{ route('accounts.store') }}" class="card card-body shadow-sm">
    @include('accounts.form', ['account' => new \App\Models\Account()])
</form>
@endsection

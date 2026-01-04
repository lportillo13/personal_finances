@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Edit Account</h1>
<form method="POST" action="{{ route('accounts.update', $account) }}" class="card card-body shadow-sm">
    @method('PUT')
    @include('accounts.form', ['account' => $account])
</form>
@endsection

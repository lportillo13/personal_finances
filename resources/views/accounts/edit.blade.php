@extends('layouts.app')

@section('title', 'Edit Account')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Edit Account</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('accounts.update', $account) }}">
            @method('PUT')
            @include('accounts.form', ['account' => $account])
        </form>
    </div>
</div>
@endsection

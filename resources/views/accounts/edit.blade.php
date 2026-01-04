@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Edit account</h1>
    <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Back</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('accounts.update', $account) }}">
            @csrf
            @method('PUT')
            @include('accounts._form', ['account' => $account, 'autopayAccounts' => $autopayAccounts])
            <button class="btn btn-primary" type="submit">Update account</button>
        </form>
    </div>
</div>
@endsection

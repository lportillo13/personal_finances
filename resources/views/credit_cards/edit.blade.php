@extends('layouts.app')

@section('title', 'Edit Credit Card')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Edit Credit Card</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('credit-cards.update', $creditCard) }}">
            @method('PUT')
            @include('credit_cards.form', ['creditCard' => $creditCard])
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Add Credit Card')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h1 class="h5 mb-0">Add Credit Card</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('credit-cards.store') }}">
            @include('credit_cards.form', ['creditCard' => new \App\Models\CreditCard()])
        </form>
    </div>
</div>
@endsection

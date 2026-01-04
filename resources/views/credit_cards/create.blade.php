@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Add Credit Card</h1>
<form method="POST" action="{{ route('credit-cards.store') }}" class="card card-body shadow-sm">
    @include('credit_cards.form', ['creditCard' => new \App\Models\CreditCard()])
</form>
@endsection

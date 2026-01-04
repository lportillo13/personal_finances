@extends('layouts.app')

@section('content')
<h1 class="h4 mb-3">Edit Credit Card</h1>
<form method="POST" action="{{ route('credit-cards.update', $creditCard) }}" class="card card-body shadow-sm">
    @method('PUT')
    @include('credit_cards.form', ['creditCard' => $creditCard])
</form>
@endsection

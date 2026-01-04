@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Forgot your password?</h1>
                <p class="text-muted">Enter your email to receive a reset link.</p>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('login') }}">Back to login</a>
                        <button class="btn btn-primary" type="submit">Send reset link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

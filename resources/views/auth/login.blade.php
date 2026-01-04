@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Login</h1>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('password.request') }}">Forgot your password?</a>
                        <button class="btn btn-primary" type="submit">Login</button>
                    </div>
                </form>
                <div class="mt-3">
                    <span class="text-muted">Need an account?</span> <a href="{{ route('register') }}">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

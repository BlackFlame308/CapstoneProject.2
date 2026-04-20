@extends('layouts.app')

@section('title', 'Login - SafeTrack')

@section('content')
<div class="card mx-auto" style="max-width: 400px;">
    <div class="card-body">
        <h3 class="card-title mb-4">Login</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                @error('password')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        @if (App\Models\User::count() === 0)
        <div class="mt-4 text-center">
            <p class="mb-2">No accounts found.</p>
            <a href="{{ route('register') }}" class="btn btn-outline-primary">Create Administrator Account</a>
        </div>
        @else
        <div class="mt-4 text-center">
            <p class="mb-2">If you do not have an account, ask your Barangay Captain or Encoder to create one for you.</p>
            <p class="small text-muted">Captains and Encoders can register new accounts from the dashboard after login.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Register - SafeTrack')

@section('content')
<div class="card mx-auto" style="max-width: 500px;">
    <div class="card-body">
        <h3 class="card-title mb-4">Register</h3>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
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

            @if (!empty($roles))
            <div class="mb-3">
                <label for="role_id" class="form-label">Role</label>
                <select id="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            @else
            <div class="alert alert-info">
                <p class="mb-0">This registration will create the first administrator account.</p>
            </div>
            @endif

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>

        <div class="mt-4 text-center">
            <p class="mb-0">Already registered? <a href="{{ route('login') }}">Login</a></p>
        </div>
    </div>
</div>
@endsection

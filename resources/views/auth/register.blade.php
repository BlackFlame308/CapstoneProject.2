@extends('layouts.auth')

@section('title', 'Register - SafeTrack')
@section('subtitle', $isFirstUser ? 'Create the first administrator account' : 'Create a user account')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name') }}" required autofocus>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror"
               id="email" name="email" value="{{ old('email') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if(!$isFirstUser)
        <div class="mb-3">
            <label for="role_id" class="form-label">Role</label>
            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                <option value="">Select role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->role_id }}" @selected(old('role_id') === $role->role_id)>
                        {{ ucfirst($role->role_name) }}
                    </option>
                @endforeach
            </select>
            @error('role_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror"
               id="password" name="password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="password_confirmation"
               name="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="fas fa-user-plus"></i> Create Account
    </button>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}">Back to login</a>
    </div>
</form>
@endsection

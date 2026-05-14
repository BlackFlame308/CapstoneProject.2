@extends('layouts.auth')

@section('title', 'Change Password - SafeTrack')
@section('subtitle', 'Update your account password')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <div class="mb-3">
        <label for="current_password" class="form-label">Current Password</label>
        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
               id="current_password" name="current_password" required autofocus>
        @error('current_password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror"
               id="password" name="password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <input type="password" class="form-control" id="password_confirmation"
               name="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="fas fa-save"></i> Change Password
    </button>
</form>
@endsection

@extends('layouts.auth')

@section('title', 'Change Password - SafeTrack')
@section('subtitle', 'Update your account password')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <div class="mb-3">
        <label for="current_password" class="form-label">Current Password</label>
        <div class="input-group">
            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                   id="current_password" name="current_password" required autofocus>
            <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('current_password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <div class="input-group">
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required>
            <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <div class="input-group">
            <input type="password" class="form-control" id="password_confirmation"
                   name="password_confirmation" required>
            <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="fas fa-save"></i> Change Password
    </button>
</form>

<script>
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.closest('.input-group').querySelector('input');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});
</script>
@endsection

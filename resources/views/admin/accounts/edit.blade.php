@extends('layouts.admin')

@section('page_title', 'Edit Account')
@section('page_icon')
    <i class="fas fa-edit"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <form action="{{ route('admin.accounts.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Personal Information -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-user"></i> Personal Information
                    </h6>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span style="color: red;">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control @error('contact_number') is-invalid @enderror"
                               id="contact_number" name="contact_number"
                               value="{{ old('contact_number', $user->contact_number) }}">
                        @error('contact_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Login Credentials -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-lock"></i> Login Credentials
                    </h6>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span style="color: red;">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror"
                               id="username" name="username" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span style="color: red;">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (Leave empty to keep current)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Minimum 8 characters if changing</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                               name="password_confirmation">
                    </div>

                    <hr>

                    <!-- Account Assignment -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-cog"></i> Account Assignment
                    </h6>

                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role <span style="color: red;">*</span></label>
                        <select class="form-select @error('role_id') is-invalid @enderror"
                                id="role_id" name="role_id" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}"
                                    @if(old('role_id', $user->role_id) == $role->role_id) selected @endif>
                                    {{ ucfirst($role->role_name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="household_id" class="form-label">Assign to Household (Optional)</label>
                        <select class="form-select @error('household_id') is-invalid @enderror"
                                id="household_id" name="household_id">
                            <option value="">-- None (For Admin Users) --</option>
                            @foreach($households as $household)
                                <option value="{{ $household->household_id }}"
                                    @if(old('household_id', $user->household_id) == $household->household_id) selected @endif>
                                    {{ $household->household_code }} - {{ $household->household_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('household_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   @if(old('is_active', $user->is_active)) checked @endif>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-check-circle"></i> Account is Active
                            </label>
                        </div>
                    </div>

                    <hr>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

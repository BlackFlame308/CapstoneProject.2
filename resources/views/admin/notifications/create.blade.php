@extends('layouts.admin')

@section('page_title', 'Create Notification')
@section('page_icon')
    <i class="fas fa-bell"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; padding: 20px; border-bottom: 2px solid #dee2e6;">
                <h5 style="margin: 0; color: #333; font-weight: 600;">
                    <i class="fas fa-plus"></i> Create New Notification
                </h5>
            </div>

            <div class="card-body" style="padding: 30px;">
                <form action="{{ route('admin.notifications.store') }}" method="POST">
                    @csrf

                    <!-- Recipient Type -->
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: #333;">
                            Send To <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="recipient_type" class="form-select @error('recipient_type') is-invalid @enderror" 
                                onchange="updateRecipientOptions()">
                            <option value="">-- Select Recipient Type --</option>
                            <option value="user" @if(old('recipient_type') === 'user') selected @endif>Specific User</option>
                            <option value="role" @if(old('recipient_type') === 'role') selected @endif>By Role</option>
                            <option value="all" @if(old('recipient_type') === 'all') selected @endif>All Users</option>
                        </select>
                        @error('recipient_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- User Selection -->
                    <div class="mb-3" id="user_select_div" style="display: none;">
                        <label class="form-label" style="font-weight: 600; color: #333;">
                            User <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">-- Select User --</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}" @if(old('user_id') === $id) selected @endif>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-3" id="role_select_div" style="display: none;">
                        <label class="form-label" style="font-weight: 600; color: #333;">
                            Role <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}" @if((string) old('role_id') === (string) $role->role_id) selected @endif>
                                    {{ $role->role_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: #333;">
                            Title <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="e.g., System Update" maxlength="255">
                        @error('title')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Message -->
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: #333;">
                            Message <span style="color: #dc3545;">*</span>
                        </label>
                        <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                  rows="5" placeholder="Enter notification message" maxlength="1000">{{ old('message') }}</textarea>
                        <small class="text-muted">Max 1000 characters</small>
                        @error('message')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Channel -->
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: #333;">
                            Channel <span style="color: #dc3545;">*</span>
                        </label>
                        <div>
                            @foreach(['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push Notification', 'in-app' => 'In-App'] as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="notification_channel" 
                                           value="{{ $value }}" id="channel_{{ $value }}"
                                           @if(old('notification_channel') === $value || ($loop->first && !old('notification_channel'))) checked @endif>
                                    <label class="form-check-label" for="channel_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('notification_channel')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Severity -->
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: #333;">
                            Severity Level <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="severity_level" class="form-select @error('severity_level') is-invalid @enderror">
                            <option value="">-- Select Severity --</option>
                            <option value="low" @if(old('severity_level') === 'low') selected @endif>Low</option>
                            <option value="medium" @if(old('severity_level') === 'medium') selected @endif>Medium</option>
                            <option value="high" @if(old('severity_level') === 'high') selected @endif>High</option>
                            <option value="critical" @if(old('severity_level') === 'critical') selected @endif>Critical</option>
                        </select>
                        @error('severity_level')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Submit Buttons -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateRecipientOptions() {
    const type = document.querySelector('[name="recipient_type"]').value;
    document.getElementById('user_select_div').style.display = type === 'user' ? 'block' : 'none';
    document.getElementById('role_select_div').style.display = type === 'role' ? 'block' : 'none';
}
updateRecipientOptions();
</script>

@endsection

@extends('layouts.admin')

@section('page_title', 'Notification Details')
@section('page_icon')
    <i class="fas fa-bell"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back to Notifications
        </a>

        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <!-- Header -->
                <div class="mb-4">
                    <h4 style="margin-bottom: 15px; color: #333; font-weight: 600;">
                        {{ $notification->title }}
                    </h4>

                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div class="row">
                            <div class="col-md-3">
                                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">Status</p>
                                @php
                                    $statusColor = match($notification->notification_status) {
                                        'sent' => '#d4edda',
                                        'failed' => '#f8d7da',
                                        'pending' => '#cfe2ff',
                                        default => '#e2e3e5'
                                    };
                                    $statusTextColor = match($notification->notification_status) {
                                        'sent' => '#155724',
                                        'failed' => '#721c24',
                                        'pending' => '#004085',
                                        default => '#383d41'
                                    };
                                @endphp
                                <span class="badge" style="background-color: {{ $statusColor }}; color: {{ $statusTextColor }}; padding: 8px 12px; display: inline-block; margin-top: 5px;">
                                    {{ ucfirst($notification->notification_status) }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">Channel</p>
                                <h6 style="margin: 5px 0; color: #333;">{{ ucfirst($notification->notification_channel) }}</h6>
                            </div>
                            <div class="col-md-3">
                                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">Severity</p>
                                @php
                                    $severityColor = match($notification->severity_level) {
                                        'critical' => '#dc3545',
                                        'high' => '#fd7e14',
                                        'medium' => '#ffc107',
                                        'low' => '#28a745',
                                        default => '#6c757d'
                                    };
                                @endphp
                                <span class="badge" style="background-color: {{ $severityColor }}; color: white; padding: 8px 12px; display: inline-block; margin-top: 5px;">
                                    {{ ucfirst($notification->severity_level) }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">Date</p>
                                <h6 style="margin: 5px 0; color: #333;">{{ $notification->created_at->format('M d, Y') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Recipient Info -->
                <div class="mb-4">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">
                        <i class="fas fa-user"></i> Recipient
                    </h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <p style="margin: 0;">
                            <strong>Name:</strong> {{ $notification->user->name ?? 'N/A' }}
                        </p>
                        <p style="margin: 8px 0;">
                            <strong>Email:</strong> {{ $notification->user->email ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <hr>

                <!-- Message -->
                <div class="mb-4">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">
                        <i class="fas fa-envelope"></i> Message
                    </h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; white-space: pre-wrap; word-break: break-word;">
                        {{ $notification->message }}
                    </div>
                </div>

                <hr>

                <!-- Actions -->
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    @if($notification->notification_status === 'failed')
                        <form action="{{ route('admin.notifications.retry', $notification) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-sync"></i> Retry
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this notification?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

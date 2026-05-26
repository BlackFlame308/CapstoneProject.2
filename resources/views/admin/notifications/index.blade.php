@extends('layouts.admin')

@section('page_title', 'Notifications Management')
@section('page_icon')
    <i class="fas fa-bell"></i>
@endsection

@section('content')
<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">📬</div>
            <div class="stat-value">{{ $totalNotifications }}</div>
            <div class="stat-label">Total Notifications</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">✓</div>
            <div class="stat-value">{{ $sentCount }}</div>
            <div class="stat-label">Sent</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">⏱️</div>
            <div class="stat-value">{{ $pendingCount }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">✗</div>
            <div class="stat-value">{{ $failedCount }}</div>
            <div class="stat-label">Failed</div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-body" style="padding: 20px;">
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Notification
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- All Status --</option>
                    <option value="pending" @if(($filters['status'] ?? '') === 'pending') selected @endif>Pending</option>
                    <option value="sent" @if(($filters['status'] ?? '') === 'sent') selected @endif>Sent</option>
                    <option value="failed" @if(($filters['status'] ?? '') === 'failed') selected @endif>Failed</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="channel" class="form-select">
                    <option value="">-- All Channels --</option>
                    <option value="email" @if(($filters['channel'] ?? '') === 'email') selected @endif>Email</option>
                    <option value="sms" @if(($filters['channel'] ?? '') === 'sms') selected @endif>SMS</option>
                    <option value="push" @if(($filters['channel'] ?? '') === 'push') selected @endif>Push</option>
                    <option value="in-app" @if(($filters['channel'] ?? '') === 'in-app') selected @endif>In-App</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" 
                       value="{{ $filters['start_date'] ?? '' }}">
            </div>

            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" 
                       value="{{ $filters['end_date'] ?? '' }}">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Notifications Table -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="table-responsive">
        @if($notifications->count() > 0)
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">Title</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Channel</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Status</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Severity</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Recipient</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Date</th>
                        <th style="font-weight: 600; color: #333; padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notif)
                        @php
                            $statusColor = match($notif->notification_status) {
                                'sent' => '#d4edda',
                                'failed' => '#f8d7da',
                                'pending' => '#cfe2ff',
                                default => '#e2e3e5'
                            };
                            $statusTextColor = match($notif->notification_status) {
                                'sent' => '#155724',
                                'failed' => '#721c24',
                                'pending' => '#004085',
                                default => '#383d41'
                            };
                            $severityColor = match($notif->severity_level) {
                                'critical' => '#dc3545',
                                'high' => '#fd7e14',
                                'medium' => '#ffc107',
                                'low' => '#28a745',
                                default => '#6c757d'
                            };
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; vertical-align: middle;">
                                <a href="{{ route('admin.notifications.show', $notif) }}" 
                                   style="color: #0066cc; text-decoration: none; font-weight: 500;">
                                    {{ Str::limit($notif->title, 50) }}
                                </a>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: #e7f3ff; color: #0066cc;">
                                    {{ ucfirst($notif->notification_channel) }}
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: {{ $statusColor }}; color: {{ $statusTextColor }};">
                                    {{ ucfirst($notif->notification_status) }}
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: {{ $severityColor }}; color: white;">
                                    {{ ucfirst($notif->severity_level) }}
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                {{ optional($notif->user)->name ?? 'N/A' }}
                            </td>
                            <td style="padding: 15px; vertical-align: middle; color: #999; font-size: 12px;">
                                {{ $notif->created_at->format('M d, Y H:i') }}
                            </td>
                            <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <a href="{{ route('admin.notifications.show', $notif) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($notif->notification_status === 'failed')
                                        <form action="{{ route('admin.notifications.retry', $notif) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" title="Retry">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.notifications.destroy', $notif) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this notification?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 60px 20px; text-align: center;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h5 style="color: #999; margin-bottom: 10px;">No notifications found</h5>
                <p style="color: #bbb;">Create a notification to get started</p>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if($notifications->lastPage() > 1)
<div class="mt-4">
    {{ $notifications->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection

@extends('layouts.admin')

@section('page_title', 'Audit Logs')
@section('page_icon')
    <i class="fas fa-history"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; font-weight: 600; color: #333;">
                System Audit Logs
            </h5>
            <form action="{{ route('admin.audit-logs.clear') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Clear logs older than 6 months?')">
                    <i class="fas fa-trash"></i> Clear Old Logs
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-body" style="padding: 20px;">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="user_id" class="form-control" placeholder="Filter by user..."
                       value="{{ $filters['user_id'] ?? '' }}">
            </div>

            <div class="col-md-2">
                <select name="action" class="form-select">
                    <option value="">-- All Actions --</option>
                    <option value="create" @if(($filters['action'] ?? '') == 'create') selected @endif>Create</option>
                    <option value="update" @if(($filters['action'] ?? '') == 'update') selected @endif>Update</option>
                    <option value="delete" @if(($filters['action'] ?? '') == 'delete') selected @endif>Delete</option>
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

            <div class="col-md-3">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="table-responsive">
        @if($logs->count() > 0)
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">User</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Action</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Model</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Details</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; vertical-align: middle;">
                                <strong style="color: #333;">{{ $log->user_id }}</strong>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                @php
                                    $actionColor = match($log->action) {
                                        'create' => '#d4edda',
                                        'delete' => '#f8d7da',
                                        'update' => '#cfe2ff',
                                        default => '#e2e3e5'
                                    };
                                    $actionTextColor = match($log->action) {
                                        'create' => '#155724',
                                        'delete' => '#721c24',
                                        'update' => '#004085',
                                        default => '#383d41'
                                    };
                                @endphp
                                <span class="badge" style="background-color: {{ $actionColor }}; color: {{ $actionTextColor }};">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <code style="background: #f1f1f1; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    {{ $log->model }}
                                </code>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="btn btn-sm btn-info">
                                    View Changes
                                </a>
                            </td>
                            <td style="padding: 15px; vertical-align: middle; color: #999; font-size: 12px;">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 60px 20px; text-align: center;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h5 style="color: #999; margin-bottom: 10px;">No audit logs found</h5>
                <p style="color: #bbb;">System activity will appear here</p>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if($logs->lastPage() > 1)
<div class="mt-4">
    {{ $logs->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection

@extends('layouts.admin')

@section('page_title', 'Import Details')
@section('page_icon')
    <i class="fas fa-file-upload"></i>
@endsection

@section('content')
<a href="{{ route('admin.csv-import.index') }}" class="btn btn-secondary mb-3">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 20px;">
                <div class="row">
                    <div class="col-md-4">
                        <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">File Name</p>
                        <h5 style="margin: 5px 0; color: #333;">{{ $import->file_name }}</h5>
                    </div>
                    <div class="col-md-4">
                        <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">Uploaded</p>
                        <h5 style="margin: 5px 0; color: #333;">{{ $import->created_at->format('M d, Y H:i:s') }}</h5>
                    </div>
                    <div class="col-md-4">
                        <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">Type</p>
                        <h5 style="margin: 5px 0; color: #333;">{{ $import->dataSource->type ?? 'N/A' }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-value">{{ number_format($import->total_records ?? 0) }}</div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">✓</div>
            <div class="stat-value">{{ number_format($import->successful_records ?? 0) }}</div>
            <div class="stat-label">Successful</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">✗</div>
            <div class="stat-value">{{ number_format($errorCount) }}</div>
            <div class="stat-label">Failed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-value">
                @php
                    $rate = ($import->total_records ?? 0) > 0 
                        ? round(($import->successful_records ?? 0) / ($import->total_records ?? 1) * 100, 1)
                        : 0;
                @endphp
                {{ $rate }}%
            </div>
            <div class="stat-label">Success Rate</div>
        </div>
    </div>
</div>

<!-- Import Logs -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-header" style="background-color: #f8f9fa; padding: 15px 20px; border-bottom: 2px solid #dee2e6;">
        <h5 style="margin: 0; color: #333; font-weight: 600;">
            <i class="fas fa-list"></i> Import Log Details
        </h5>
    </div>
    <div class="table-responsive">
        @if($logs->count() > 0)
            <table class="table" style="margin: 0; font-size: 13px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="font-weight: 600; color: #333; padding: 12px;">Row</th>
                        <th style="font-weight: 600; color: #333; padding: 12px;">Status</th>
                        <th style="font-weight: 600; color: #333; padding: 12px;">Message</th>
                        <th style="font-weight: 600; color: #333; padding: 12px;">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        @php
                            $statusColor = $log->status === 'success' ? '#d4edda' : '#f8d7da';
                            $statusTextColor = $log->status === 'success' ? '#155724' : '#721c24';
                            $statusText = ucfirst($log->status);
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 12px; vertical-align: middle;">
                                <code style="background: #f1f1f1; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                    {{ $log->row_num }}
                                </code>
                            </td>
                            <td style="padding: 12px; vertical-align: middle;">
                                <span class="badge" style="background-color: {{ $statusColor }}; color: {{ $statusTextColor }};">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td style="padding: 12px; vertical-align: middle;">
                                @if($log->error_message)
                                    <small style="color: #666;">{{ $log->error_message }}</small>
                                @else
                                    <small style="color: #999;">Processed successfully</small>
                                @endif
                            </td>
                            <td style="padding: 12px; vertical-align: middle; color: #999; font-size: 11px;">
                                {{ $log->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 40px 20px; text-align: center;">
                <p style="color: #999;">No log entries available</p>
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

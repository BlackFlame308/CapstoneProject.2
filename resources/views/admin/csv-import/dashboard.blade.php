@extends('layouts.admin')

@section('page_title', 'CSV Import Dashboard')
@section('page_icon')
    <i class="fas fa-file-upload"></i>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-value">{{ $totalImports }}</div>
            <div class="stat-label">Total Imports</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">✓</div>
            <div class="stat-value">{{ $successRate }}%</div>
            <div class="stat-label">Success Rate</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-value">{{ number_format($totalRecords) }}</div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value">{{ number_format($failedRecords) }}</div>
            <div class="stat-label">Failed Records</div>
        </div>
    </div>
</div>

<!-- Recent Errors -->
@if($recentErrors->count() > 0)
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none; border-top: 4px solid #dc3545;">
    <div class="card-header" style="background-color: #f8f9fa; padding: 15px 20px; border-bottom: 2px solid #dee2e6;">
        <h5 style="margin: 0; color: #333; font-weight: 600;">
            <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i> Recent Import Errors
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table" style="margin: 0;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 15px;">Import File</th>
                    <th style="padding: 15px;">Row</th>
                    <th style="padding: 15px;">Error Message</th>
                    <th style="padding: 15px;">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentErrors as $error)
                <tr style="border-bottom: 1px solid #f1f1f1;">
                    <td style="padding: 15px;">
                        <strong style="color: #333;">{{ $error->dataSource->type ?? 'N/A' }}</strong>
                    </td>
                    <td style="padding: 15px;">
                        <code>Row {{ $error->row_num }}</code>
                    </td>
                    <td style="padding: 15px; color: #dc3545;">
                        {{ Str::limit($error->error_message, 100) }}
                    </td>
                    <td style="padding: 15px; color: #999; font-size: 12px;">
                        {{ $error->created_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Import History -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-header" style="background-color: #f8f9fa; padding: 15px 20px; border-bottom: 2px solid #dee2e6;">
        <h5 style="margin: 0; color: #333; font-weight: 600;">
            <i class="fas fa-history"></i> Import History
        </h5>
    </div>
    <div class="table-responsive">
        @if($uploads->count() > 0)
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">File Name</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Total</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Success</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Failed</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Success Rate</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Date</th>
                        <th style="font-weight: 600; color: #333; padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uploads as $upload)
                        @php
                            $rate = $upload->total_records > 0 
                                ? round($upload->successful_records / $upload->total_records * 100, 1)
                                : 0;
                            $rateColor = $rate >= 90 ? '#d4edda' : ($rate >= 70 ? '#fff3cd' : '#f8d7da');
                            $rateTextColor = $rate >= 90 ? '#155724' : ($rate >= 70 ? '#856404' : '#721c24');
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; vertical-align: middle;">
                                <strong style="color: #333;">{{ $upload->file_name }}</strong>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                {{ number_format($upload->total_records ?? 0) }}
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: #d4edda; color: #155724;">
                                    {{ number_format($upload->successful_records ?? 0) }}
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                @if(($upload->failed_records ?? 0) > 0)
                                    <span class="badge" style="background-color: #f8d7da; color: #721c24;">
                                        {{ number_format($upload->failed_records) }}
                                    </span>
                                @else
                                    <span class="badge" style="background-color: #e9ecef; color: #6c757d;">0</span>
                                @endif
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: {{ $rateColor }}; color: {{ $rateTextColor }};">
                                    {{ $rate }}%
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle; color: #999; font-size: 12px;">
                                {{ $upload->created_at->format('M d, Y H:i') }}
                            </td>
                            <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="{{ route('admin.csv-import.show', $upload) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Details
                                    </a>
                                    @if(($upload->failed_records ?? 0) > 0)
                                        <form action="{{ route('admin.csv-import.retry', $upload) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" title="Retry failed rows">
                                                <i class="fas fa-sync"></i> Retry
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.csv-import.destroy', $upload) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this import record?')">
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
                <h5 style="color: #999; margin-bottom: 10px;">No imports yet</h5>
                <p style="color: #bbb;">Start by uploading a CSV file</p>
                <a href="{{ route('csv.upload') }}" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload CSV
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if($uploads->lastPage() > 1)
<div class="mt-4">
    {{ $uploads->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection

@extends('layouts.admin')

@section('page_title', 'Rescue Reports')
@section('page_icon')
    <i class="fas fa-life-ring"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; font-weight: 600; color: #333;">
                Rescue Reports
            </h5>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-body" style="padding: 20px;">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">-- All Status --</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Incident Type</label>
                <select name="incident_type" class="form-select">
                    <option value="">-- All Types --</option>
                    <option value="fire">Fire</option>
                    <option value="flood">Flood</option>
                    <option value="medical">Medical Emergency</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Rescue Reports Table -->
@if($reports->count() > 0)
    <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">Dispatch Code</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Responder Name</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Rescue Team</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Assigned Area</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Priority</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Status</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; color: #333; font-weight: 600; font-family: monospace;">{{ $report->assignment_code }}</td>
                            <td style="padding: 15px; color: #555;">{{ $report->responder_name ?? 'N/A' }}</td>
                            <td style="padding: 15px; color: #555;">{{ $report->team_name ?? 'N/A' }}</td>
                            <td style="padding: 15px; color: #555;">{{ $report->assigned_area ?? 'N/A' }}</td>
                            <td style="padding: 15px;">
                                <span class="badge {{ $report->priority_level === 'high' ? 'bg-danger' : ($report->priority_level === 'medium' ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                                    {{ ucfirst($report->priority_level ?? 'low') }}
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <span class="badge {{ $report->status === 'completed' ? 'bg-success' : 'bg-primary' }}">
                                    {{ ucfirst($report->status ?? 'dispatched') }}
                                </span>
                            </td>
                            <td style="padding: 15px; color: #999;">
                                {{ \Carbon\Carbon::parse($report->assigned_at)->format('M d, Y g:i A') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <!-- Empty State / Placeholder -->
    <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
        <div style="padding: 80px 20px; text-align: center;">
            <i class="fas fa-inbox" style="font-size: 64px; color: #ddd; margin-bottom: 20px; display: block;"></i>
            <h4 style="color: #999; margin-bottom: 10px;">No Rescue Operations</h4>
            <p style="color: #bbb; margin-bottom: 20px;">
                No rescue operations matched your filters.
            </p>
        </div>
    </div>
@endif

@endsection

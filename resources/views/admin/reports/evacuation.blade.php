@extends('layouts.admin')

@section('page_title', 'Evacuation Reports')
@section('page_icon')
    <i class="fas fa-building"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; font-weight: 600; color: #333;">
                Evacuation Reports
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
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Evacuation Reports Table -->
@if($reports->count() > 0)
    <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">Event / Disaster</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Evacuation Center</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Household</th>
                        <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Evacuated Count</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Method</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Verified By</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; color: #333; font-weight: 500;">{{ $report->event_name ?? 'N/A' }}</td>
                            <td style="padding: 15px; color: #555;">{{ $report->center_name ?? 'N/A' }}</td>
                            <td style="padding: 15px; color: #555;">
                                <div>
                                    <strong>{{ $report->household_name ?? 'N/A' }}</strong> 
                                    @if(!empty($report->household_code))
                                        <span class="text-muted" style="font-size: 12px; margin-left: 4px;">({{ $report->household_code }})</span>
                                    @endif
                                </div>
                                @if(!empty($report->purok_sitio) || !empty($report->barangay_name))
                                    <small style="color: #6c757d; display: block; margin-top: 4px; font-size: 11px;">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                        {{ trim(($report->purok_sitio ? $report->purok_sitio . ', ' : '') . ($report->barangay_name ?? '')) }}
                                    </small>
                                @endif
                            </td>
                            <td style="padding: 15px; text-align: right; font-weight: 600; color: #4f46e5;">{{ $report->evacuated_count ?? 0 }}</td>
                            <td style="padding: 15px;">
                                <span class="badge {{ $report->method === 'qr' ? 'bg-success' : 'bg-secondary' }}" style="white-space: nowrap;">
                                    {{ strtoupper($report->method ?? 'manual') }}
                                </span>
                            </td>
                            <td style="padding: 15px; color: #555;">{{ $report->verified_by ?? 'System' }}</td>
                            <td style="padding: 15px; color: #999;">
                                {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y g:i A') }}
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
            <h4 style="color: #999; margin-bottom: 10px;">No Evacuation Reports</h4>
            <p style="color: #bbb; margin-bottom: 20px;">
                No evacuation records matched your filters.
            </p>
        </div>
    </div>
@endif

@endsection

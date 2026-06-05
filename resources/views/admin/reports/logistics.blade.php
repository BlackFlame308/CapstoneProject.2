@extends('layouts.admin')

@section('page_title', 'Logistics Reports')
@section('page_icon')
    <i class="fas fa-boxes"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; font-weight: 600; color: #333;">
                Logistics Reports
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
                    <option value="pending">Pending</option>
                    <option value="distributed">Distributed</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Item Type</label>
                <select name="item_type" class="form-select">
                    <option value="">-- All Types --</option>
                    <option value="food">Food & Supplies</option>
                    <option value="medical">Medical Supplies</option>
                    <option value="equipment">Equipment</option>
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

<!-- Logistics Reports Table -->
@if($reports->count() > 0)
    <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">Request Code</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Item Name</th>
                        <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Quantity</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Evacuation Center</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Requested By</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Urgency</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Status</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; color: #333; font-weight: 600; font-family: monospace;">{{ $report->request_id }}</td>
                            <td style="padding: 15px; color: #333; font-weight: 500;">{{ $report->item_name }}</td>
                            <td style="padding: 15px; text-align: right; font-weight: 600; color: #f59e0b;">
                                {{ $report->quantity }} {{ $report->unit ?? 'pcs' }}
                            </td>
                            <td style="padding: 15px; color: #555;">{{ $report->center_name ?? 'N/A' }}</td>
                            <td style="padding: 15px; color: #555;">{{ $report->requested_by ?? 'N/A' }}</td>
                            <td style="padding: 15px;">
                                <span class="badge {{ $report->urgency_label === 'Urgent' || $report->urgency_label === 'High' ? 'bg-danger' : 'bg-secondary' }}">
                                    {{ $report->urgency_label ?? 'Standard' }}
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <span class="badge {{ $report->status_key === 'completed' || $report->status_key === 'distributed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $report->status_label ?? 'Pending' }}
                                </span>
                            </td>
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
            <h4 style="color: #999; margin-bottom: 10px;">No Logistics Reports</h4>
            <p style="color: #bbb; margin-bottom: 20px;">
                No logistics reports matched your filters.
            </p>
        </div>
    </div>
@endif

@endsection

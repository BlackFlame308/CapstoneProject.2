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

<!-- Empty State / Placeholder -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div style="padding: 80px 20px; text-align: center;">
        <i class="fas fa-inbox" style="font-size: 64px; color: #ddd; margin-bottom: 20px; display: block;"></i>
        <h4 style="color: #999; margin-bottom: 10px;">No Evacuation Reports</h4>
        <p style="color: #bbb; margin-bottom: 20px;">
            Reports will appear here once the evacuation subsystem is connected via API.
        </p>

        <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; border-radius: 8px; text-align: left; display: inline-block;">
            <p style="margin: 0 0 10px 0; color: #333; font-weight: 600;">
                <i class="fas fa-wrench"></i> Integration Checklist
            </p>
            <ul style="margin: 0; padding-left: 20px; color: #666; font-size: 14px;">
                <li>Create API endpoint for evacuation data</li>
                <li>Implement token-based authentication</li>
                <li>Add error handling and logging</li>
                <li>Test API connection and data format</li>
                <li>Configure caching strategy</li>
            </ul>
        </div>
    </div>
</div>

@endsection

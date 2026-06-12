@extends('layouts.admin')

@section('page_title', 'Reports')
@section('page_icon')
    <i class="fas fa-file-alt"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h5 style="margin: 0 0 20px 0; font-weight: 600; color: #333;">
            Reports from Subsystems
        </h5>
    </div>
</div>

<!-- Report Categories -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('admin.reports.evacuation') }}" style="text-decoration: none;">
            <div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #667eea 100%); border-radius: 12px; border: none; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-body" style="padding: 30px; text-align: center; color: white;">
                    <i class="fas fa-building" style="font-size: 40px; margin-bottom: 15px; display: block; opacity: 0.9;"></i>
                    <h5 style="margin: 0 0 10px 0; font-weight: 600;">Evacuation Reports ({{ $evacuationCount }})</h5>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">Disaster evacuation incidents</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('admin.reports.rescue') }}" style="text-decoration: none;">
            <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; border: none; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-body" style="padding: 30px; text-align: center; color: white;">
                    <i class="fas fa-life-ring" style="font-size: 40px; margin-bottom: 15px; display: block; opacity: 0.9;"></i>
                    <h5 style="margin: 0 0 10px 0; font-weight: 600;">Rescue Operations ({{ $rescueCount }})</h5>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">Rescue and emergency operations</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <a href="{{ route('admin.reports.logistics') }}" style="text-decoration: none;">
            <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); border-radius: 12px; border: none; cursor: pointer; transition: all 0.3s ease;">
                <div class="card-body" style="padding: 30px; text-align: center; color: white;">
                    <i class="fas fa-boxes" style="font-size: 40px; margin-bottom: 15px; display: block; opacity: 0.9;"></i>
                    <h5 style="margin: 0 0 10px 0; font-weight: 600;">Logistics Requests ({{ $logisticsCount }})</h5>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">Aid and supply distribution</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- API Integration Notice -->
<div class="row">
    <div class="col-12">
        @if($evacuationCount == 0 && $rescueCount == 0 && $logisticsCount == 0)
            <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none; border-left: 4px solid #ffc107;">
                <div class="card-body" style="padding: 30px; text-align: center;">
                    <i class="fas fa-info-circle" style="font-size: 40px; color: #ffc107; margin-bottom: 15px; display: block;"></i>
                    <h5 style="margin: 0 0 10px 0; color: #333;">Reports Not Yet Available</h5>
                    <p style="margin: 0 0 15px 0; color: #999;">
                        Reports will appear here once subsystems are connected via API integration.
                    </p>
                    <small style="color: #bbb;">
                        This is currently a placeholder. API endpoints for evacuation, rescue, and logistics systems need to be configured.
                    </small>
                </div>
            </div>
        @else
            <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none; border-left: 4px solid #10b981;">
                <div class="card-body" style="padding: 30px; text-align: center;">
                    <i class="fas fa-check-circle" style="font-size: 40px; color: #10b981; margin-bottom: 15px; display: block;"></i>
                    <h5 style="margin: 0 0 10px 0; color: #333; font-weight: 600;">Subsystem Integrations Connected</h5>
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 15px;">
                        Evacuation, Rescue, and Logistics subsystem APIs are active and reporting data.
                    </p>
                    <small style="color: #999;">
                        All data is synchronized and available. Select any category card above to view detailed reports.
                    </small>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@extends('layouts.admin')

@section('page_title', 'Dashboard')
@section('page_icon')
    <i class="fas fa-chart-line"></i>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-home"></i></div>
            <div class="stat-value">{{ $totalHouseholds }}</div>
            <div class="stat-label">Total Households</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ $totalPopulation }}</div>
            <div class="stat-label">Total Population</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-child"></i></div>
            <div class="stat-value">{{ $childrenCount }}</div>
            <div class="stat-label">Children (< 18)</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-crutches"></i></div>
            <div class="stat-value">{{ $pwdCount }}</div>
            <div class="stat-label">PWD Count</div>
        </div>
    </div>
</div>

<!-- Additional Statistics -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-wheelchair"></i></div>
            <div class="stat-value">{{ $seniorsCount }}</div>
            <div class="stat-label">Seniors (60+)</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sitio Rankings -->
    <div class="col-lg-6 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-map-pin"></i> Sitio Rankings (Most Populated)
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if($sitioRankings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm" style="background: transparent;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="font-weight: 600; color: #333;">Sitio/Purok</th>
                                    <th style="font-weight: 600; color: #333; text-align: right;">Population</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sitioRankings as $sitio)
                                    <tr style="border-bottom: 1px solid #f1f1f1;">
                                        <td style="padding: 12px; color: #555;">{{ $sitio->purok_sitio ?? 'Unknown' }}</td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600; color: #667eea;">
                                            {{ $sitio->member_count }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 30px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                        <p>No sitio data available yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Households -->
    <div class="col-lg-6 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-history"></i> Recent Households
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if($recentHouseholds->count() > 0)
                    <div class="list-group">
                        @foreach($recentHouseholds as $household)
                            <a href="{{ route('admin.households.show', $household) }}"
                               class="list-group-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border: none; border-bottom: 1px solid #f1f1f1; text-decoration: none; color: inherit;">
                                <div>
                                    <h6 style="margin: 0 0 5px 0; font-weight: 600; color: #333;">
                                        {{ $household->household_code }}
                                    </h6>
                                    <small style="color: #999;">
                                        {{ $household->address?->purok_sitio ?? 'No location' }}
                                    </small>
                                </div>
                                <span class="badge" style="background-color: #667eea; color: white; padding: 8px 12px; border-radius: 20px;">
                                    {{ $household->members->count() }} members
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 30px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                        <p>No households found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Latest Reports Section -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h6 style="margin: 0; font-weight: 600; color: #333;">
                        <i class="fas fa-file-alt"></i> Latest Reports from Subsystems
                    </h6>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-primary" style="text-decoration: none;">
                        View All <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="card-body" style="padding: 40px; text-align: center;">
                <div style="color: #999;">
                    <i class="fas fa-info-circle" style="font-size: 32px; margin-bottom: 15px; display: block;"></i>
                    <h5>No subsystem data available yet</h5>
                    <p style="margin-bottom: 0;">Waiting for API integration with:</p>
                    <ul style="list-style: none; padding: 0; margin-top: 10px;">
                        <li><i class="fas fa-check text-success me-1"></i> Evacuation System</li>
                        <li><i class="fas fa-check text-success me-1"></i> Rescue System</li>
                        <li><i class="fas fa-check text-success me-1"></i> Logistics System</li>
                    </ul>
                    <small style="display: block; margin-top: 15px; color: #bbb;">
                        Reports will appear here once subsystems are connected via API.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; border-left: 4px solid #667eea;">
            <h6 style="margin: 0 0 15px 0; font-weight: 600; color: #333;">Quick Actions</h6>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('admin.households.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Household
                </a>
                @if(auth()->user()?->canManageAccounts())
                    <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                @endif
                <a href="{{ route('admin.households.index') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-list"></i> View All Households
                </a>
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-chart-bar"></i> View Analytics
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

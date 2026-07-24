@extends('layouts.admin')

@section('page_title', 'Dashboard')
@section('page_icon')
    <i class="fas fa-chart-line"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg,#0d2338 0%,#1a3a5c 100%); border-radius: 14px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <div class="card-body" style="padding: 22px;">
                <h6 style="margin: 0 0 12px 0; font-weight: 700; color: #fff; font-size: 14px;">
                    <i class="fas fa-compass me-2" style="color:#22d492;"></i>Core Principles
                </h6>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <span class="badge" style="background:rgba(29,184,126,0.2);color:#22d492;border:1px solid rgba(29,184,126,0.3);">Data Management</span>
                    <span class="badge" style="background:rgba(6,182,212,0.18);color:#67e8f9;border:1px solid rgba(6,182,212,0.3);">Monitoring</span>
                    <span class="badge" style="background:rgba(29,184,126,0.15);color:#86efac;border:1px solid rgba(29,184,126,0.25);">Viewing Reports</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="fas fa-home"></i></div>
            <div class="stat-value">{{ $totalHouseholds }}</div>
            <div class="stat-label">Total Households</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card info">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ $totalPopulation }}</div>
            <div class="stat-label">Total Population</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card warning">
            <div class="stat-icon"><i class="fas fa-child"></i></div>
            <div class="stat-value">{{ $childrenCount }}</div>
            <div class="stat-label">Children (&lt; 18)</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card danger">
            <div class="stat-icon"><i class="fas fa-wheelchair"></i></div>
            <div class="stat-value">{{ $pwdCount }}</div>
            <div class="stat-label">PWD Count</div>
        </div>
    </div>
</div>

<!-- Additional Statistics -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card success">
            <div class="stat-icon"><i class="fas fa-person-cane"></i></div>
            <div class="stat-value">{{ $seniorsCount }}</div>
            <div class="stat-label">Seniors (60+)</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card purple">
            <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
            <div class="stat-value">{{ $adultsCount }}</div>
            <div class="stat-label">Adults (18-59)</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="fas fa-heart"></i></div>
            <div class="stat-value">{{ $pregnantCount }}</div>
            <div class="stat-label">Pregnant</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sitio Rankings -->
    <div class="col-lg-6 mb-4">
        <div class="card" style="background: white; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg,#f0f6ff,#e8f2fb); border-bottom: 2px solid rgba(26,58,92,0.12); padding: 18px 20px;">
                <h6 style="margin: 0; font-weight: 700; color: #1a3a5c;">
                    <i class="fas fa-map-pin me-2" style="color:#1db87e;"></i>Sitio Rankings (Most Vulnerable)
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if($sitioRankings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm" style="background: transparent;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(26,58,92,0.12);">
                                    <th style="font-weight: 700; color: #1a3a5c; font-size:12px;">Sitio/Purok</th>
                                    <th style="font-weight: 700; color: #ef4444; text-align: right; font-size:12px;">Vulnerable</th>
                                    <th style="font-weight: 700; color: #1a3a5c; text-align: right; font-size:12px;">Population</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sitioRankings as $sitio)
                                    <tr style="border-bottom: 1px solid #f1f1f1;">
                                        <td style="padding: 12px; color: #555;">{{ $sitio->purok_sitio ?? 'Unknown' }}</td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600; color: #e74c3c;">
                                            {{ $sitio->vulnerable_count }}
                                        </td>
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
        <div class="card" style="background: white; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg,#f0f6ff,#e8f2fb); border-bottom: 2px solid rgba(26,58,92,0.12); padding: 18px 20px;">
                <h6 style="margin: 0; font-weight: 700; color: #1a3a5c;">
                    <i class="fas fa-history me-2" style="color:#1db87e;"></i>Recent Households
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if($recentHouseholds->count() > 0)
                    <div class="list-group">
                        @foreach($recentHouseholds as $household)
                            <a href="{{ route('admin.households.show', $household) }}"
                               class="list-group-item" style="display: flex; justify-content: space-between; align-items: center; padding: 13px 0; border: none; border-bottom: 1px solid rgba(0,0,0,0.05); text-decoration: none; color: inherit; transition: background 0.2s; border-radius: 0;">
                                <div>
                                    <h6 style="margin: 0 0 4px 0; font-weight: 700; color: #1a3a5c; font-size:13.5px;">
                                        {{ $household->household_code }}
                                    </h6>
                                    <small style="color: #9ca3af;">
                                        {{ $household->address?->purok_sitio ?? 'No location' }}
                                    </small>
                                </div>
                                <span class="badge" style="background: linear-gradient(135deg,#1db87e,#159962); color: white; padding: 7px 12px; border-radius: 20px; font-size:11px;">
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


<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div style="background: linear-gradient(135deg, #0d2338 0%, #1a3a5c 100%); border-radius: 14px; padding: 22px; border-left: 4px solid #1db87e; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h6 style="margin: 0 0 14px 0; font-weight: 700; color: #fff; font-size: 14px;">
                <i class="fas fa-bolt me-2" style="color:#22d492;"></i>Quick Actions
            </h6>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('admin.households.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-pen"></i> Manual Household Entry
                </a>
                <a href="{{ route('csv.upload') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-csv"></i> Upload Households via CSV
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

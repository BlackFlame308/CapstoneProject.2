@extends('layouts.admin')

@section('page_title', 'Analytics & Statistics')
@section('page_icon')
    <i class="fas fa-chart-bar"></i>
@endsection

@section('content')
<!-- Barangay Selector -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h5 style="margin: 0 0 5px 0; color: #333; font-weight: 600;">Active Barangay Filter</h5>
                    <p style="margin: 0; color: #999; font-size: 13px;">
                        Showing analytics specifically for <strong>{{ $selectedBarangay ? $selectedBarangay->name : 'All Barangay' }}</strong>.
                    </p>
                </div>
                <div>
                    <form action="{{ route('admin.analytics.index') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
                        <select name="barangay_id" class="form-select" style="min-width: 250px; border-radius: 8px; border: 1px solid #dee2e6; padding: 8px 12px;" onchange="this.form.submit()">
                            @foreach($availableBarangays as $b)
                                <option value="{{ $b->barangay_id }}" {{ $selectedBarangayId == $b->barangay_id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->city?->name ?? 'Unknown City' }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Statistics -->
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
            <div class="stat-value">{{ $totalMembers }}</div>
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
            <div class="stat-label">PWD</div>
        </div>
    </div>
</div>

<!-- Additional Metrics -->
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

<!-- Demographics Tables -->
<div class="row">
    <!-- Age Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-chart-bar"></i> Age Distribution
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="font-weight: 600; color: #333; padding: 15px;">Age Range</th>
                            <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Count</th>
                            <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = $ageDistribution->sum('count'); @endphp
                        @foreach($ageDistribution as $age)
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="padding: 15px; color: #555;">{{ $age['range'] }}</td>
                                <td style="padding: 15px; text-align: right; font-weight: 600; color: #667eea;">
                                    {{ $age['count'] }}
                                </td>
                                <td style="padding: 15px; text-align: right; color: #999;">
                                    {{ $total > 0 ? number_format(($age['count'] / $total) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gender Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-venus-mars"></i> Gender Distribution
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="font-weight: 600; color: #333; padding: 15px;">Gender</th>
                            <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Count</th>
                            <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = $genderDistribution->sum('count'); @endphp
                        @foreach($genderDistribution as $gender)
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="padding: 15px; color: #555;">{{ $gender['type'] }}</td>
                                <td style="padding: 15px; text-align: right; font-weight: 600; color: #667eea;">
                                    {{ $gender['count'] }}
                                </td>
                                <td style="padding: 15px; text-align: right; color: #999;">
                                    {{ $total > 0 ? number_format(($gender['count'] / $total) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Civil Status & Education -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-ring"></i> Civil Status
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="font-weight: 600; color: #333; padding: 15px;">Status</th>
                            <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($civilStatus as $status)
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="padding: 15px; color: #555;">{{ $status->civil_status }}</td>
                                <td style="padding: 15px; text-align: right; font-weight: 600; color: #667eea;">
                                    {{ $status->count }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="padding: 20px; text-align: center; color: #999;">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-graduation-cap"></i> Education Level
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="font-weight: 600; color: #333; padding: 15px;">Level</th>
                            <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($educationLevel as $edu)
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="padding: 15px; color: #555;">{{ $edu->education_level }}</td>
                                <td style="padding: 15px; text-align: right; font-weight: 600; color: #667eea;">
                                    {{ $edu->count }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="padding: 20px; text-align: center; color: #999;">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Sitio Distribution -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-map-pin"></i> Sitio Vulnerability Rankings (Sorted by Vulnerable Count)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="font-weight: 600; color: #333; padding: 15px;">Sitio/Purok</th>
                            <th style="font-weight: 600; color: #e74c3c; padding: 15px; text-align: right;">Vulnerable</th>
                            <th style="font-weight: 600; color: #333; padding: 15px; text-align: right;">Total Population</th>
                            <th style="font-weight: 600; color: #333; padding: 15px;">Vulnerability Density Ratio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sitioDistribution as $sitio)
                            @php 
                                $percent = $sitio->population > 0 ? ($sitio->vulnerable_count / $sitio->population) * 100 : 0;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="padding: 15px; color: #555;">{{ $sitio->sitio_name }}</td>
                                <td style="padding: 15px; font-weight: 700; color: #e74c3c; text-align: right;">
                                    {{ $sitio->vulnerable_count }}
                                </td>
                                <td style="padding: 15px; font-weight: 600; color: #667eea; text-align: right;">
                                    {{ $sitio->population }}
                                </td>
                                <td style="padding: 15px;">
                                    <div style="background-color: #f1f1f1; height: 20px; border-radius: 10px; overflow: hidden;">
                                        <div style="background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); height: 100%; width: {{ $percent }}%; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: bold;">
                                            @if($percent > 0) {{ number_format($percent, 1) }}% @else 0% @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding: 20px; text-align: center; color: #999;">No sitio data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

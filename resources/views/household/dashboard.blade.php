@extends('layouts.household')

@section('title', 'My Household Dashboard - SafeTrack')

@section('content')
<!-- Reassuring Greeting & Safety Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); border-radius: 16px; padding: 30px; color: white; box-shadow: 0 4px 20px rgba(30, 58, 138, 0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h2 style="font-weight: 700; margin-bottom: 5px;">Hello, {{ $household->household_name ?? 'Household Head' }}!</h2>
                <p style="margin: 0; opacity: 0.9; font-size: 15px;">
                    Welcome to your SafeTrack Portal. Keep your family demographics and emergency contacts monitored.
                </p>
            </div>
            <div>
                <div style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 30px; padding: 10px 24px; display: flex; align-items: center; gap: 10px;">
                    <span style="width: 12px; height: 12px; border-radius: 50%; background-color: #10b981; display: inline-block; box-shadow: 0 0 10px #10b981;"></span>
                    <strong style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Household is Safe</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Access Scope Notice -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: none;">
            <div class="card-body" style="padding: 20px;">
                <h6 style="margin: 0 0 12px 0; font-weight: 700; color: #333;">
                    <i class="fas fa-user-shield"></i> Resident Account Access
                </h6>
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                    Your household account can view SafeTrack and connected subsystem information in read-only mode.
                </p>
                <div style="display: flex; gap: 18px; flex-wrap: wrap;">
                    <div style="min-width: 260px;">
                        <small style="display:block; color:#16a34a; font-weight:700; margin-bottom:6px;">ALLOWED</small>
                        <small style="display:block; color:#555;">Login, view household details, family members, analytics, and reports.</small>
                    </div>
                    <div style="min-width: 260px;">
                        <small style="display:block; color:#dc2626; font-weight:700; margin-bottom:6px;">NOT INCLUDED</small>
                        <small style="display:block; color:#555;">No encoding, no system-wide edits, no report submission, and no admin controls.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Basic Demographics Analytics (Cards) -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card" style="border-top-color: #3b82f6;">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value">{{ $totalMembers }}</div>
            <div class="stat-label">Total Family Members</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card" style="border-top-color: #f59e0b;">
            <div class="stat-icon"><i class="fas fa-child"></i></div>
            <div class="stat-value">{{ $childrenCount }}</div>
            <div class="stat-label">Children (< 18)</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card" style="border-top-color: #8b5cf6;">
            <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
            <div class="stat-value">{{ $seniorsCount }}</div>
            <div class="stat-label">Seniors (60+)</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card" style="border-top-color: #ef4444;">
            <div class="stat-icon"><i class="fas fa-wheelchair"></i></div>
            <div class="stat-value">{{ $pwdCount }}</div>
            <div class="stat-label">PWD Members</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Household Details & Evacuation Info -->
    <div class="col-lg-5 mb-4">
        <!-- Household Information Card -->
        <div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-home"></i> Household Details
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; font-size: 11px;">Household Code</small>
                        <p style="margin: 0; color: #333; font-weight: 600; font-size: 15px;">{{ $household->household_code }}</p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; font-size: 11px;">Assigned Location</small>
                        <p style="margin: 0; color: #333; font-weight: 500; font-size: 14px;">
                            {{ $household->address?->purok_sitio ?? 'N/A' }}, 
                            {{ $household->address?->barangay?->name ?? 'No Barangay' }}, 
                            {{ $household->address?->barangay?->city?->name ?? 'No City' }}
                        </p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; font-size: 11px;">Vulnerability Rating</small>
                        <span class="badge" style="background-color: @if($household->vulnerability_badge === 'Critical') #fecaca @elseif($household->vulnerability_badge === 'High') #ffedd5 @else #dbeafe @endif; color: @if($household->vulnerability_badge === 'Critical') #dc2626 @elseif($household->vulnerability_badge === 'High') #d97706 @else #2563eb @endif; font-weight: 600;">
                            {{ $household->vulnerability_badge }} (Score: {{ $household->vulnerability_score }})
                        </span>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; font-size: 11px;">Contact Number</small>
                        <p style="margin: 0; color: #555; font-size: 14px;">{{ $household->contact_number ?? 'Not registered' }}</p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; font-size: 11px;">Emergency Hotline</small>
                        <p style="margin: 0; color: #ef4444; font-weight: 700; font-size: 14px;">
                            <i class="fas fa-phone-alt me-1"></i> Barangay Rescue Command Center (Call 911 / Local)
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pre-disaster Evacuation Plan Card -->
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-map-marked-alt"></i> Assigned Evacuation Site
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; gap: 15px; align-items: start;">
                    <div style="font-size: 32px; color: #1e40af;"><i class="fas fa-school"></i></div>
                    <div>
                        <h6 style="margin: 0 0 5px 0; font-weight: 600; color: #333;">Barangay Sports Complex & High School Gym</h6>
                        <p style="margin: 0 0 10px 0; color: #666; font-size: 13px;">Zone 2, Main Highway road, Cebu Province</p>
                        <span class="badge bg-success" style="padding: 5px 10px; font-weight: 500;">Status: Ready / Open</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Members Grid (Read-only View) -->
    <div class="col-lg-7 mb-4">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: none; height: 100%;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-users-viewfinder"></i> Registered Family Members ({{ $household->members->count() }})
                </h6>
            </div>
            <div class="table-responsive">
                @if($household->members->count() > 0)
                    <table class="table" style="margin: 0; vertical-align: middle;">
                        <thead>
                            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="font-weight: 600; color: #333; padding: 15px;">Full Name</th>
                                <th style="font-weight: 600; color: #333; padding: 15px;">Relation</th>
                                <th style="font-weight: 600; color: #333; padding: 15px; text-align: center;">Age</th>
                                <th style="font-weight: 600; color: #333; padding: 15px;">Special Indicators</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($household->members as $member)
                                <tr style="border-bottom: 1px solid #f1f1f1;">
                                    <td style="padding: 15px;">
                                        <strong style="color: #333;">{{ $member->name }}</strong>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span class="badge bg-light text-dark" style="border: 1px solid #ddd; font-weight: 500;">{{ $member->relation ?? 'Member' }}</span>
                                    </td>
                                    <td style="padding: 15px; text-align: center; font-weight: 600; color: #1e3a8a;">
                                        {{ $member->age ?? 'N/A' }}
                                    </td>
                                    <td style="padding: 15px;">
                                        @if($member->is_pwd)
                                            <span class="badge" style="background-color: #fff3cd; color: #856404;"><i class="fas fa-wheelchair me-1"></i> PWD</span>
                                        @endif
                                        @if($member->is_pregnant)
                                            <span class="badge" style="background-color: #f8d7da; color: #721c24;"><i class="fas fa-heart me-1"></i> Pregnant</span>
                                        @endif
                                        @if($member->age >= 60)
                                            <span class="badge bg-secondary"><i class="fas fa-user-clock me-1"></i> Senior</span>
                                        @endif
                                        @if($member->age < 18)
                                            <span class="badge bg-info text-white"><i class="fas fa-child me-1"></i> Child</span>
                                        @endif
                                        @if(!$member->is_pwd && !$member->is_pregnant && $member->age >= 18 && $member->age < 60)
                                            <span class="text-muted" style="font-size: 13px;">No special needs</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding: 60px 20px; text-align: center;">
                        <i class="fas fa-user-slash" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                        <h6 style="color: #999;">No registered family members found</h6>
                        <small style="color: #bbb;">Please contact your local Barangay Admin to register your household members.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Real-time Emergency Updates (Read-only Reports Section) -->
<div class="row">
    <div class="col-12">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-broadcast-tower"></i> Emergency Alerts & Read-only Reports
                </h6>
            </div>
            <div class="card-body" style="padding: 30px; text-align: center;">
                <div style="color: #888;">
                    <i class="fas fa-satellite-dish" style="font-size: 32px; color: #3b82f6; margin-bottom: 15px; display: block;"></i>
                    <h6 style="font-weight: 600; color: #333;">No active emergency advisories from the Subsystems</h6>
                    <p style="margin: 0 auto; max-width: 500px; font-size: 13px; color: #999;">
                        When real-time updates are published by the evacuation, logistics, or rescue modules, they will securely display here in read-only format for your guidance.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

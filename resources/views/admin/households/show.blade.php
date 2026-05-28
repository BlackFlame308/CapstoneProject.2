@extends('layouts.admin')

@section('page_title', 'Household Details')
@section('page_icon')
    <i class="fas fa-home"></i>
@endsection

@section('content')
<!-- Header with Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h3 style="margin: 0 0 10px 0; color: #333; font-weight: 600;">
                    {{ $household->household_code }}
                </h3>
                <p style="margin: 0; color: #999; font-size: 14px;">
                    <i class="fas fa-map-pin"></i> {{ $household->address?->purok_sitio ?? 'No location assigned' }}
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('admin.households.edit', $household) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @if(auth()->user()?->canDeleteHouseholds())
                    <form action="{{ route('admin.households.destroy', $household) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this household?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.households.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Household Information -->
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-info-circle"></i> Basic Information
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Code</small>
                        <p style="margin: 0; color: #333; font-weight: 500;">{{ $household->household_code }}</p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Name</small>
                        <p style="margin: 0; color: #333; font-weight: 500;">{{ $household->household_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Total Members</small>
                        <p style="margin: 0; color: #333; font-weight: 500;">{{ $household->members->count() }}</p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Created</small>
                        <p style="margin: 0; color: #333; font-weight: 500;">{{ $household->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-3">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-phone"></i> Contact Information
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: grid; gap: 15px;">
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Contact Number</small>
                        <p style="margin: 0; color: #333; font-weight: 500;">
                            {{ $household->contact_number ?? 'Not provided' }}
                        </p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Email</small>
                        <p style="margin: 0; color: #333; font-weight: 500;">
                            {{ $household->email ?? 'Not provided' }}
                        </p>
                    </div>
                    <div>
                        <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Emergency Contact</small>
                        <p style="margin: 0; color: #333; font-weight: 500;">
                            {{ $household->emergency_contact ?? 'Not provided' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Household Account & Credentials -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-user-shield"></i> Household Account & Credentials
                </h6>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if($household->user)
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Account Name</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">{{ $household->user->name }}</p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Username</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">{{ $household->user->username }}</p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Email Address</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">{{ $household->user->email }}</p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Temporary Password</small>
                            @if($household->user->temp_password)
                                <p style="margin: 0; color: #dc3545; font-weight: 700; font-family: monospace; font-size: 15px;">
                                    {{ $household->user->temp_password }}
                                </p>
                            @else
                                <p style="margin: 0; color: #28a745; font-weight: 600; font-size: 14px;">
                                    <i class="fas fa-check-circle"></i> Changed by user
                                </p>
                            @endif
                        </div>
                    </div>
                @else
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span style="color: #999;"><i class="fas fa-exclamation-triangle text-warning me-1"></i> No system account associated with this household.</span>
                        @if(auth()->user()?->canManageAccounts())
                            <a href="{{ route('admin.accounts.create', ['household_id' => $household->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus"></i> Create Account for Household
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Location Information -->
@if($household->address)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                    <h6 style="margin: 0; font-weight: 600; color: #333;">
                        <i class="fas fa-map"></i> Location Information
                    </h6>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Region</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">
                                {{ $household->address->barangay?->city?->province?->region?->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Province</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">
                                {{ $household->address->barangay?->city?->province?->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">City</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">
                                {{ $household->address->barangay?->city?->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Barangay</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">
                                {{ $household->address->barangay?->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Purok/Sitio</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">
                                {{ $household->address->purok_sitio ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 5px; font-weight: 600;">Street Address</small>
                            <p style="margin: 0; color: #333; font-weight: 500;">
                                {{ $household->address->street_address ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Members Section -->
<div class="row">
    <div class="col-12">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-users"></i> Household Members ({{ $household->members->count() }})
                </h6>
                <a href="{{ route('admin.residents.create', $household) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus"></i> Add Member
                </a>
            </div>

            <div class="table-responsive">
                @if($household->members->count() > 0)
                    <table class="table" style="margin: 0;">
                        <thead>
                            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="font-weight: 600; color: #333; padding: 15px;">Name</th>
                                <th style="font-weight: 600; color: #333; padding: 15px;">Relation</th>
                                <th style="font-weight: 600; color: #333; padding: 15px;">Age</th>
                                <th style="font-weight: 600; color: #333; padding: 15px;">Status</th>
                                <th style="font-weight: 600; color: #333; padding: 15px;">Special</th>
                                <th style="font-weight: 600; color: #333; padding: 15px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($household->members as $member)
                                <tr style="border-bottom: 1px solid #f1f1f1;">
                                    <td style="padding: 15px; vertical-align: middle;">
                                        <strong style="color: #333;">{{ $member->name }}</strong><br>
                                        <small style="color: #999;">{{ $member->birth_date?->format('M d, Y') ?? 'DOB: N/A' }}</small>
                                    </td>
                                    <td style="padding: 15px; vertical-align: middle;">
                                        <small style="color: #555;">{{ $member->relation ?? 'N/A' }}</small>
                                    </td>
                                    <td style="padding: 15px; vertical-align: middle;">
                                        {{ $member->age ?? 'N/A' }}
                                    </td>
                                    <td style="padding: 15px; vertical-align: middle;">
                                        <span class="badge" style="background-color: #667eea; color: white;">
                                            {{ $member->civil_status ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px; vertical-align: middle;">
                                        @if($member->is_pwd)
                                            <span class="badge badge-warning" style="background-color: #fff3cd; color: #856404;">PWD</span>
                                        @endif
                                        @if($member->is_pregnant)
                                            <span class="badge badge-danger" style="background-color: #f8d7da; color: #721c24;">Pregnant</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <a href="{{ route('admin.residents.edit', $member) }}"
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(auth()->user()?->canDeleteHouseholds())
                                                <form action="{{ route('admin.residents.destroy', $member) }}"
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding: 40px 20px; text-align: center;">
                        <i class="fas fa-user" style="font-size: 32px; color: #ddd; margin-bottom: 10px; display: block;"></i>
                        <p style="color: #999; margin-bottom: 20px;">No members added yet</p>
                        <a href="{{ route('admin.residents.create', $household) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus"></i> Add First Member
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

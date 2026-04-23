@extends('layouts.app')

@section('title', 'Household Details - SafeTrack')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
<h1>Household Details
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('households.index') }}" class="btn btn-secondary">Back to List</a>
            @can('manage_households')
            <a href="{{ route('households.edit', $household) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('households.destroy', $household) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
            @endcan
        </div>
    </div>

    <!-- Household Information Card -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Household Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 40%;">Household Code:</th>
                                <td>{{ $household->household_code }}</td>
                            </tr>
                            <tr>
                                <th>Contact Number:</th>
                                <td>{{ $household->contact_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Emergency Contact:</th>
                                <td>{{ $household->emergency_contact ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created Date:</th>
                                <td>{{ $household->created_at ? $household->created_at->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Total Members:</th>
                                <td><span class="badge bg-primary">{{ $household->members->count() }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Address Information Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Address Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 40%;">Street:</th>
                                <td>{{ $household->address->street ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Purok/Sitio:</th>
                                <td>{{ $household->address->purok ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Barangay:</th>
                                <td>{{ $household->address->barangay->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>City/Municipality:</th>
                                <td>{{ $household->address->barangay->city->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Province:</th>
                                <td>{{ $household->address->barangay->city->province->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Region:</th>
                                <td>{{ $household->address->barangay->city->province->region->name ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Household Head Information Card -->
    @if($household->user)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Household Head</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 20%;">Name:</th>
                                <td>{{ $household->user->name }}</td>
                                <th style="width: 20%;">Email:</th>
                                <td>{{ $household->user->email }}</td>
                            </tr>
                            <tr>
                                <th>Role:</th>
                                <td>{{ $household->user->role->name ?? 'N/A' }}</td>
                                <th>Account Status:</th>
                                <td>
                                    @if($household->user->must_change_password)
                                        <span class="badge bg-warning">Password Change Required</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                            </tr>
                            @can('isCaptain')
                            <tr>
                                <th>Temp Password:</th>
                                <td colspan="3">
                                    @if($household->user->temp_password)
                                        <code class="bg-light p-2 rounded">{{ $household->user->temp_password }}</code>
                                        <small class="text-muted d-block mt-1">Share with household head</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endcan
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Members List Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Household Members ({{ $household->members->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($household->members->isEmpty())
                        <p class="text-muted">No members recorded for this household.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Birth Date</th>
                                        <th>Age</th>
                                        <th>Sex</th>
                                        <th>Civil Status</th>
                                        <th>Education</th>
                                        <th>Profession</th>
                                        <th>PWD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($household->members as $member)
                                    <tr>
                                        <td>{{ $member->first_name }} {{ $member->middle_name }} {{ $member->last_name }}</td>
                                        <td>{{ $member->birth_date ? $member->birth_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            @if($member->birth_date)
                                                {{ \Carbon\Carbon::parse($member->birth_date)->age }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $member->sex ?? 'N/A' }}</td>
                                        <td>{{ $member->civil_status ?? 'N/A' }}</td>
                                        <td>{{ $member->education_level ?? 'N/A' }}</td>
                                        <td>{{ $member->profession ?? 'N/A' }}</td>
                                        <td>
                                            @if($member->is_pwd)
                                                <span class="badge bg-warning">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('households._household_login_info')

@endsection


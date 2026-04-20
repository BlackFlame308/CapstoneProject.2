@extends('layouts.app')

@section('title', 'Create Household - SafeTrack')

@section('content')
<div class="container">
    <h1 class="mb-4">Create New Household</h1>
    
    <form action="{{ route('households.store') }}" method="POST" id="householdForm">
        @csrf
        
        <div class="row">
            <!-- Address Section -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Address Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="region-select" class="form-label">Region</label>
                            <select name="region_id" class="form-control @error('region_id') is-invalid @enderror" id="region-select">
                                <option value="">Select Region</option>
                            </select>
                            @error('region_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="province-select" class="form-label">Province</label>
                            <select name="province_id" class="form-control @error('province_id') is-invalid @enderror" id="province-select">
                                <option value="">Select Province</option>
                            </select>
                            @error('province_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="city-select" class="form-label">City</label>
                            <select name="city_id" class="form-control @error('city_id') is-invalid @enderror" id="city-select">
                                <option value="">Select City</option>
                            </select>
                            @error('city_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="barangay-select" class="form-label">Barangay <span class="text-danger">*</span></label>
                            <select name="barangay_id" class="form-control @error('barangay_id') is-invalid @enderror" id="barangay-select" required>
                                <option value="">Select Barangay</option>
                            </select>
                            @error('barangay_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="street" class="form-label">Street</label>
                            <input type="text" name="street" id="street" class="form-control @error('street') is-invalid @enderror" value="{{ old('street') }}">
                            @error('street')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="purok" class="form-label">Purok/Sitio</label>
                            <input type="text" name="purok" id="purok" class="form-control @error('purok') is-invalid @enderror" value="{{ old('purok') }}">
                            @error('purok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Household Information Section -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Household Head Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="head_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="head_first_name" id="head_first_name" class="form-control @error('head_first_name') is-invalid @enderror" value="{{ old('head_first_name') }}" required>
                            @error('head_first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="head_middle_name" class="form-label">Middle Name</label>
                            <input type="text" name="head_middle_name" id="head_middle_name" class="form-control @error('head_middle_name') is-invalid @enderror" value="{{ old('head_middle_name') }}">
                            @error('head_middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="head_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="head_last_name" id="head_last_name" class="form-control @error('head_last_name') is-invalid @enderror" value="{{ old('head_last_name') }}" required>
                            @error('head_last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number') }}">
                            @error('contact_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" id="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror" value="{{ old('emergency_contact') }}">
                            @error('emergency_contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Household Members</h5>
                <button type="button" class="btn btn-sm btn-success" onclick="addMemberRow()">+ Add Member</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="membersTable">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                <th>Birth Date</th>
                                <th>Sex</th>
                                <th>Civil Status</th>
                                <th>Education</th>
                                <th>Profession</th>
                                <th>PWD</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="membersBody">
                            <tr class="member-row">
                                <td><input type="text" name="members[0][first_name]" class="form-control form-control-sm" required></td>
                                <td><input type="text" name="members[0][middle_name]" class="form-control form-control-sm"></td>
                                <td><input type="text" name="members[0][last_name]" class="form-control form-control-sm" required></td>
                                <td><input type="date" name="members[0][birth_date]" class="form-control form-control-sm" required></td>
                                <td>
                                    <select name="members[0][sex]" class="form-control form-control-sm" required>
                                        <option value="">Select</option>
                                        <option value="M">M</option>
                                        <option value="F">F</option>
                                    </select>
                                </td>
                                <td><input type="text" name="members[0][civil_status]" class="form-control form-control-sm"></td>
                                <td><input type="text" name="members[0][education_level]" class="form-control form-control-sm"></td>
                                <td><input type="text" name="members[0][profession]" class="form-control form-control-sm"></td>
                                <td>
                                    <input type="checkbox" name="members[0][is_pwd]" class="form-check-input" value="1">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeMemberRow(this)">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Household</button>
            <a href="{{ route('households.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
let memberCount = 1;

function addMemberRow() {
    const tbody = document.getElementById('membersBody');
    const row = document.createElement('tr');
    row.className = 'member-row';
    row.innerHTML = `
        <td><input type="text" name="members[${memberCount}][first_name]" class="form-control form-control-sm" required></td>
        <td><input type="text" name="members[${memberCount}][middle_name]" class="form-control form-control-sm"></td>
        <td><input type="text" name="members[${memberCount}][last_name]" class="form-control form-control-sm" required></td>
        <td><input type="date" name="members[${memberCount}][birth_date]" class="form-control form-control-sm" required></td>
        <td>
            <select name="members[${memberCount}][sex]" class="form-control form-control-sm" required>
                <option value="">Select</option>
                <option value="M">M</option>
                <option value="F">F</option>
            </select>
        </td>
        <td><input type="text" name="members[${memberCount}][civil_status]" class="form-control form-control-sm"></td>
        <td><input type="text" name="members[${memberCount}][education_level]" class="form-control form-control-sm"></td>
        <td><input type="text" name="members[${memberCount}][profession]" class="form-control form-control-sm"></td>
        <td>
            <input type="checkbox" name="members[${memberCount}][is_pwd]" class="form-check-input" value="1">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeMemberRow(this)">Remove</button>
        </td>
    `;
    tbody.appendChild(row);
    memberCount++;
}

function removeMemberRow(btn) {
    btn.closest('tr').remove();
}

// Load location hierarchy
document.addEventListener('DOMContentLoaded', function() {
    fetchRegions();

    document.getElementById('region-select').addEventListener('change', function() {
        fetchProvinces(this.value);
    });

    document.getElementById('province-select').addEventListener('change', function() {
        fetchCities(this.value);
    });

    document.getElementById('city-select').addEventListener('change', function() {
        fetchBarangays(this.value);
    });

    function fetchRegions() {
        fetch('/api/regions')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('region-select');
                select.innerHTML = '<option value="">Select Region</option>';
                data.forEach(region => {
                    select.innerHTML += `<option value="${region.id}">${region.name}</option>`;
                });
            });
    }

    function fetchProvinces(regionId) {
        if (!regionId) {
            document.getElementById('province-select').innerHTML = '<option value="">Select Province</option>';
            return;
        }
        fetch(`/api/regions/${regionId}/provinces`)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('province-select');
                select.innerHTML = '<option value="">Select Province</option>';
                data.forEach(province => {
                    select.innerHTML += `<option value="${province.id}">${province.name}</option>`;
                });
            });
    }

    function fetchCities(provinceId) {
        if (!provinceId) {
            document.getElementById('city-select').innerHTML = '<option value="">Select City</option>';
            return;
        }
        fetch(`/api/provinces/${provinceId}/cities`)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('city-select');
                select.innerHTML = '<option value="">Select City</option>';
                data.forEach(city => {
                    select.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                });
            });
    }

    function fetchBarangays(cityId) {
        if (!cityId) {
            document.getElementById('barangay-select').innerHTML = '<option value="">Select Barangay</option>';
            return;
        }
        fetch(`/api/cities/${cityId}/barangays`)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('barangay-select');
                select.innerHTML = '<option value="">Select Barangay</option>';
                data.forEach(barangay => {
                    select.innerHTML += `<option value="${barangay.id}">${barangay.name}</option>`;
                });
            });
    }
});
</script>

<style>
.table-responsive {
    font-size: 13px;
}
.form-control-sm {
    height: auto;
    padding: 4px 8px;
}
</style>
@endsection
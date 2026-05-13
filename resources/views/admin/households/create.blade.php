@extends('layouts.admin')

@section('page_title', 'Create New Household')
@section('page_icon')
    <i class="fas fa-plus-circle"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <form action="{{ route('admin.households.store') }}" method="POST">
                    @csrf

                    <!-- Household Code -->
                    <div class="mb-3">
                        <label for="household_code" class="form-label">
                            <i class="fas fa-barcode"></i> Household Code <span style="color: red;">*</span>
                        </label>
                        <input type="text" class="form-control @error('household_code') is-invalid @enderror"
                               id="household_code" name="household_code" placeholder="e.g., HH-001"
                               value="{{ old('household_code') }}" required>
                        @error('household_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Unique identifier for this household</small>
                    </div>

                    <!-- Household Name -->
                    <div class="mb-3">
                        <label for="household_name" class="form-label">
                            <i class="fas fa-home"></i> Household Name (Optional)
                        </label>
                        <input type="text" class="form-control @error('household_name') is-invalid @enderror"
                               id="household_name" name="household_name"
                               placeholder="e.g., Santos Family" value="{{ old('household_name') }}">
                        @error('household_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Location Section -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-map"></i> Location Information
                    </h6>

                    <!-- Region -->
                    <div class="mb-3">
                        <label for="region_id" class="form-label">
                            <i class="fas fa-globe"></i> Region
                        </label>
                        <select class="form-select @error('region_id') is-invalid @enderror"
                                id="region_id" name="region_id">
                            <option value="">-- Select Region --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}"
                                    @if(old('region_id') == $region->id) selected @endif>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('region_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Province -->
                    <div class="mb-3">
                        <label for="province_id" class="form-label">
                            <i class="fas fa-landmark"></i> Province
                        </label>
                        <select class="form-select @error('province_id') is-invalid @enderror"
                                id="province_id" name="province_id">
                            <option value="">-- Select Province --</option>
                        </select>
                        @error('province_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- City -->
                    <div class="mb-3">
                        <label for="city_id" class="form-label">
                            <i class="fas fa-city"></i> City
                        </label>
                        <select class="form-select @error('city_id') is-invalid @enderror"
                                id="city_id" name="city_id">
                            <option value="">-- Select City --</option>
                        </select>
                        @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Barangay -->
                    <div class="mb-3">
                        <label for="barangay_id" class="form-label">
                            <i class="fas fa-map-pin"></i> Barangay
                        </label>
                        <select class="form-select @error('barangay_id') is-invalid @enderror"
                                id="barangay_id" name="barangay_id">
                            <option value="">-- Select Barangay --</option>
                        </select>
                        @error('barangay_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Purok/Sitio -->
                    <div class="mb-3">
                        <label for="purok_sitio" class="form-label">
                            <i class="fas fa-location-dot"></i> Purok/Sitio
                        </label>
                        <input type="text" class="form-control @error('purok_sitio') is-invalid @enderror"
                               id="purok_sitio" name="purok_sitio" placeholder="e.g., Purok 1"
                               value="{{ old('purok_sitio') }}">
                        @error('purok_sitio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Street Address -->
                    <div class="mb-3">
                        <label for="street_address" class="form-label">
                            <i class="fas fa-road"></i> Street Address
                        </label>
                        <input type="text" class="form-control @error('street_address') is-invalid @enderror"
                               id="street_address" name="street_address"
                               placeholder="e.g., Main Street, Building 5" value="{{ old('street_address') }}">
                        @error('street_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Contact Information -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-phone"></i> Contact Information
                    </h6>

                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control @error('contact_number') is-invalid @enderror"
                               id="contact_number" name="contact_number" placeholder="09XX-XXX-XXXX"
                               value="{{ old('contact_number') }}">
                        @error('contact_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" placeholder="example@email.com"
                               value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="emergency_contact" class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror"
                               id="emergency_contact" name="emergency_contact"
                               placeholder="Emergency contact information" value="{{ old('emergency_contact') }}">
                        @error('emergency_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('admin.households.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Household
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // API Helper function
    function fetchLocationData(url, selectId) {
        const regionId = document.getElementById('region_id').value;
        const provinceId = document.getElementById('province_id').value;
        const cityId = document.getElementById('city_id').value;

        if (selectId === 'province_id' && !regionId) return;
        if (selectId === 'city_id' && !provinceId) return;
        if (selectId === 'barangay_id' && !cityId) return;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById(selectId);
                select.innerHTML = '<option value="">-- Select ' + selectId.replace('_id', '') + ' --</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }

    // Region change
    document.getElementById('region_id').addEventListener('change', function() {
        const regionId = this.value;
        if (regionId) {
            fetchLocationData(`/locations/provinces/${regionId}`, 'province_id');
        }
    });

    // Province change
    document.getElementById('province_id').addEventListener('change', function() {
        const provinceId = this.value;
        if (provinceId) {
            fetchLocationData(`/locations/cities/${provinceId}`, 'city_id');
        }
    });

    // City change
    document.getElementById('city_id').addEventListener('change', function() {
        const cityId = this.value;
        if (cityId) {
            fetchLocationData(`/locations/barangays/${cityId}`, 'barangay_id');
        }
    });
</script>
@endpush

@endsection

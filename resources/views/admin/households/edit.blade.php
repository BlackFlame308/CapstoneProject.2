@extends('layouts.admin')

@section('page_title', 'Edit Household')
@section('page_icon')
    <i class="fas fa-edit"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <form action="{{ route('admin.households.update', $household) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Household Name -->
                    <div class="mb-3">
                        <label for="household_name" class="form-label">
                            <i class="fas fa-home"></i> Household Name
                        </label>
                        <input type="text" class="form-control @error('household_name') is-invalid @enderror"
                               id="household_name" name="household_name"
                               value="{{ old('household_name', $household->household_name) }}">
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
                        <label for="region_id" class="form-label">Region</label>
                        <select class="form-select @error('region_id') is-invalid @enderror"
                                id="region_id" name="region_id">
                            <option value="">-- Select Region --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}"
                                    @if(old('region_id', $household->address?->barangay?->city?->province?->region_id) == $region->id) selected @endif>
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
                        <label for="province_id" class="form-label">Province</label>
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
                        <label for="city_id" class="form-label">City</label>
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
                        <label for="barangay_id" class="form-label">Barangay</label>
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
                        <label for="purok_sitio" class="form-label">Purok/Sitio</label>
                        <input type="text" class="form-control @error('purok_sitio') is-invalid @enderror"
                               id="purok_sitio" name="purok_sitio"
                               value="{{ old('purok_sitio', $household->address?->purok_sitio) }}">
                        @error('purok_sitio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Street Address -->
                    <div class="mb-3">
                        <label for="street_address" class="form-label">Street Address</label>
                        <input type="text" class="form-control @error('street_address') is-invalid @enderror"
                               id="street_address" name="street_address"
                               value="{{ old('street_address', $household->address?->street_address) }}">
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
                               id="contact_number" name="contact_number"
                               value="{{ old('contact_number', $household->contact_number) }}">
                        @error('contact_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email"
                               value="{{ old('email', $household->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="emergency_contact" class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror"
                               id="emergency_contact" name="emergency_contact"
                               value="{{ old('emergency_contact', $household->emergency_contact) }}">
                        @error('emergency_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('admin.households.show', $household) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
                if (data && data.data) {
                    data.data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }

    document.getElementById('region_id').addEventListener('change', function() {
        if (this.value) {
            fetchLocationData(`/locations/provinces/${this.value}`, 'province_id');
        }
    });

    document.getElementById('province_id').addEventListener('change', function() {
        if (this.value) {
            fetchLocationData(`/locations/cities/${this.value}`, 'city_id');
        }
    });

    document.getElementById('city_id').addEventListener('change', function() {
        if (this.value) {
            fetchLocationData(`/locations/barangays/${this.value}`, 'barangay_id');
        }
    });
</script>
@endpush

@endsection

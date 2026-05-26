@extends('layouts.admin')

@section('page_title', 'Add Vulnerable Group')
@section('page_icon')
    <i class="fas fa-plus"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <form action="{{ route('admin.vulnerable-groups.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="vulnerable_group_key" class="form-label">
                            <i class="fas fa-key"></i> Group Key <span style="color: red;">*</span>
                        </label>
                        <input type="text" class="form-control @error('vulnerable_group_key') is-invalid @enderror"
                               id="vulnerable_group_key" name="vulnerable_group_key" 
                               placeholder="e.g., pwdperson, elderly, pregnant, child"
                               value="{{ old('vulnerable_group_key') }}" required>
                        @error('vulnerable_group_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Unique identifier (lowercase, no spaces)</small>
                    </div>

                    <div class="mb-3">
                        <label for="vulnerable_group_label" class="form-label">
                            <i class="fas fa-tag"></i> Display Label <span style="color: red;">*</span>
                        </label>
                        <input type="text" class="form-control @error('vulnerable_group_label') is-invalid @enderror"
                               id="vulnerable_group_label" name="vulnerable_group_label" 
                               placeholder="e.g., Person with Disability, Elderly, Pregnant, Child"
                               value="{{ old('vulnerable_group_label') }}" required maxlength="20">
                        @error('vulnerable_group_label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Human-readable name (max 20 characters)</small>
                    </div>

                    <hr>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('admin.vulnerable-groups.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Group
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

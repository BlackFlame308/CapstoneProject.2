@extends('layouts.admin')

@section('page_title', 'Edit Member')
@section('page_icon')
    <i class="fas fa-edit"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <p style="margin: 0; color: #999; font-size: 14px;">
                    <i class="fas fa-home"></i> Household: <strong>{{ $household->household_code }}</strong>
                </p>
            </div>
            <div class="card-body" style="padding: 30px;">

                <form action="{{ route('admin.residents.update', $member) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Personal Information -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-user"></i> Personal Information
                    </h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label">First Name <span style="color: red;">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   id="first_name" name="first_name" value="{{ old('first_name', $member->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                   id="middle_name" name="middle_name" value="{{ old('middle_name', $member->middle_name) }}">
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="last_name" class="form-label">Last Name <span style="color: red;">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                   id="last_name" name="last_name" value="{{ old('last_name', $member->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">Birth Date</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                   id="birth_date" name="birth_date" value="{{ old('birth_date', $member->birth_date?->format('Y-m-d')) }}">
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="sex" class="form-label">Sex <span style="color: red;">*</span></label>
                            <select class="form-select @error('sex') is-invalid @enderror" id="sex" name="sex" required>
                                <option value="">-- Select Sex --</option>
                                <option value="M" @if(old('sex', $member->sex) == 'M') selected @endif>Male</option>
                                <option value="F" @if(old('sex', $member->sex) == 'F') selected @endif>Female</option>
                            </select>
                            @error('sex')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <!-- Family Relationship -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-link"></i> Family Relationship
                    </h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="relation" class="form-label">Relation to Head <span style="color: red;">*</span></label>
                            <select class="form-select @error('relation') is-invalid @enderror" id="relation" name="relation" required>
                                <option value="">-- Select Relation --</option>
                                <option value="Head" @if(old('relation', $member->relation) == 'Head') selected @endif>Head of Household</option>
                                <option value="Spouse" @if(old('relation', $member->relation) == 'Spouse') selected @endif>Spouse</option>
                                <option value="Child" @if(old('relation', $member->relation) == 'Child') selected @endif>Child</option>
                                <option value="Parent" @if(old('relation', $member->relation) == 'Parent') selected @endif>Parent</option>
                                <option value="Sibling" @if(old('relation', $member->relation) == 'Sibling') selected @endif>Sibling</option>
                                <option value="Grandchild" @if(old('relation', $member->relation) == 'Grandchild') selected @endif>Grandchild</option>
                                <option value="Others" @if(old('relation', $member->relation) == 'Others') selected @endif>Others</option>
                            </select>
                            @error('relation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="civil_status" class="form-label">Civil Status <span style="color: red;">*</span></label>
                            <select class="form-select @error('civil_status') is-invalid @enderror" id="civil_status" name="civil_status" required>
                                <option value="">-- Select Status --</option>
                                <option value="Single" @if(old('civil_status', $member->civil_status) == 'Single') selected @endif>Single</option>
                                <option value="Married" @if(old('civil_status', $member->civil_status) == 'Married') selected @endif>Married</option>
                                <option value="Widowed" @if(old('civil_status', $member->civil_status) == 'Widowed') selected @endif>Widowed</option>
                                <option value="Separated" @if(old('civil_status', $member->civil_status) == 'Separated') selected @endif>Separated</option>
                            </select>
                            @error('civil_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <!-- Education & Occupation -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-briefcase"></i> Education & Occupation
                    </h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="education_level" class="form-label">Education Level</label>
                            <select class="form-select @error('education_level') is-invalid @enderror" id="education_level" name="education_level">
                                <option value="">-- Select Level --</option>
                                <option value="Elementary" @if(old('education_level', $member->education_level) == 'Elementary') selected @endif>Elementary</option>
                                <option value="High School" @if(old('education_level', $member->education_level) == 'High School') selected @endif>High School</option>
                                <option value="College" @if(old('education_level', $member->education_level) == 'College') selected @endif>College</option>
                                <option value="Post Graduate" @if(old('education_level', $member->education_level) == 'Post Graduate') selected @endif>Post Graduate</option>
                            </select>
                            @error('education_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control @error('occupation') is-invalid @enderror"
                                   id="occupation" name="occupation" value="{{ old('occupation', $member->occupation) }}">
                            @error('occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <!-- Special Needs & Status -->
                    <h6 style="margin-bottom: 20px; font-weight: 600; color: #333;">
                        <i class="fas fa-heart"></i> Special Status & Needs
                    </h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_pwd" name="is_pwd" value="1"
                                       @if(old('is_pwd', $member->is_pwd)) checked @endif>
                                <label class="form-check-label" for="is_pwd">
                                    <i class="fas fa-wheelchair"></i> Person with Disability (PWD)
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_pregnant" name="is_pregnant" value="1"
                                       @if(old('is_pregnant', $member->is_pregnant)) checked @endif>
                                <label class="form-check-label" for="is_pregnant">
                                    <i class="fas fa-heart"></i> Pregnant
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="special_needs" class="form-label">Special Needs / Notes</label>
                        <textarea class="form-control @error('special_needs') is-invalid @enderror"
                                  id="special_needs" name="special_needs" rows="3">{{ old('special_needs', $member->special_needs) }}</textarea>
                        @error('special_needs')
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

@endsection

@extends('layouts.app')

@section('title', 'CSV Upload - SafeTrack')

@section('content')
<div class="container">
    <h1 class="mb-4">Bulk Upload Households from CSV</h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload CSV File</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>CSV Format Requirements:</h6>
                        <p>Your CSV file should have the following columns in this order:</p>
                        <ul class="mb-0">
                            <li><strong>head_first_name</strong> - Household head first name (required)</li>
                            <li><strong>head_middle_name</strong> - Household head middle name (optional)</li>
                            <li><strong>head_last_name</strong> - Household head last name (required)</li>
                            <li><strong>street</strong> - Street address (optional)</li>
                            <li><strong>purok</strong> - Sitio/Purok (optional)</li>
                            <li><strong>barangay_id</strong> - Barangay ID (required)</li>
                            <li><strong>contact_number</strong> - Contact number (optional)</li>
                            <li><strong>emergency_contact</strong> - Emergency contact (optional)</li>
                            <li><strong>member_first_name</strong> - Member first name (optional)</li>
                            <li><strong>member_middle_name</strong> - Member middle name (optional)</li>
                            <li><strong>member_last_name</strong> - Member last name (optional)</li>
                            <li><strong>member_birth_date</strong> - Member birth date (optional)</li>
                            <li><strong>member_sex</strong> - Member sex: M or F (optional)</li>
                            <li><strong>member_civil_status</strong> - Member civil status (optional)</li>
                            <li><strong>member_education_level</strong> - Member education level (optional)</li>
                            <li><strong>member_profession</strong> - Member profession (optional)</li>
                            <li><strong>member_is_pwd</strong> - Member is PWD: Y or N (optional)</li>
                        </ul>
                    </div>

                    <form action="{{ route('csv.upload.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Upload and Import</button>
                        <a href="{{ route('households.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sample CSV Template</h5>
                </div>
                <div class="card-body">
                    <p>Download a sample template or use this format:</p>
                    <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">head_first_name,head_middle_name,head_last_name,street,purok,barangay_id,contact_number,emergency_contact,member_first_name,member_middle_name,member_last_name,member_birth_date,member_sex,member_civil_status,member_education_level,member_profession,member_is_pwd
Juan,M,Dela Cruz,123 Main St,Purok 1,1,09123456789,09987654321,Juan,M,Dela Cruz,1990-05-15,M,Married,High School,Farmer,N
Maria,A,Santos,456 Oak Ave,Purok 2,2,09234567890,09876543210,Maria,A,Santos,1988-03-22,F,Single,College,Teacher,N</pre>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Import Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted mb-1">Successful Imports</p>
                        <p class="h4">{{ \App\Models\ImportLog::where('status', 'success')->count() }}</p>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-1">Failed Imports</p>
                        <p class="h4">{{ \App\Models\ImportLog::where('status', 'failed')->count() }}</p>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-1">Total Processed</p>
                        <p class="h4">{{ \App\Models\ImportLog::count() }}</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tips for Success</h5>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>Use UTF-8 encoding for CSV files</li>
                        <li>Ensure all required fields are filled</li>
                        <li>Verify barangay IDs are correct</li>
                        <li>Check date format: YYYY-MM-DD</li>
                        <li>Use M/F for sex, Y/N for PWD status</li>
                        <li>Max file size: 10MB</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

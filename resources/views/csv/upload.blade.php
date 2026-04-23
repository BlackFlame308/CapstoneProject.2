@extends('layouts.app')

@section('title', 'Bulk CSV Upload - SafeTrack')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-csv me-2"></i>Bulk Household Import
                    </h3>
                </div>
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('csv.upload.process') }}" method="POST" enctype="multipart/form-data" id="csvForm">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    <input type="file" class="form-control @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" accept=".csv" required>
                                    <label for="csv_file">
                                        <i class="fas fa-file-csv me-1"></i>Choose CSV File
                                    </label>
                                    @error('csv_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-lg w-100" id="uploadBtn">
                                    <i class="fas fa-upload me-2"></i>Upload & Import
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- CSV Format Guide -->
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>CSV Format (17 Columns Required)
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Column</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td><code>head_first_name</code></td>
                                            <td>Required</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td><code>head_middle_name</code></td>
                                            <td>Optional</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td><code>head_last_name</code></td>
                                            <td>Required</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td><code>street</code></td>
                                            <td>Optional</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        <tr>
                                            <td>5</td>
                                            <td><code>purok</code></td>
                                            <td>Optional</td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td><code>barangay_id</code></td>
                                            <td>Required (numeric ID)</td>
                                        </tr>
                                        <tr>
                                            <td>7</td>
                                            <td><code>contact_number</code></td>
                                            <td>Optional</td>
                                        </tr>
                                        <tr>
                                            <td>8</td>
                                            <td><code>emergency_contact</code></td>
                                            <td>Optional</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Member Columns -->
                    <h6 class="text-info mb-3">
                        <i class="fas fa-users me-2"></i>Member Columns (9-17, Optional - One Member per Row)
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-info">
                                <tr>
                                    <th>#</th>
                                    <th>Column</th>
                                    <th>Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>9</td>
                                    <td><code>member_first_name</code></td>
                                    <td>Juan</td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td><code>member_middle_name</code></td>
                                    <td>M</td>
                                </tr>
                                <tr>
                                    <td>11</td>
                                    <td><code>member_last_name</code></td>
                                    <td>Dela Cruz</td>
                                </tr>
                                <tr>
                                    <td>12</td>
                                    <td><code>member_birth_date</code></td>
                                    <td>15/05/1990 or 1990-05-15</td>
                                </tr>
                                <tr>
                                    <td>13</td>
                                    <td><code>member_sex</code></td>
                                    <td>M or F</td>
                                </tr>
                                <tr>
                                    <td>14</td>
                                    <td><code>member_civil_status</code></td>
                                    <td>Married</td>
                                </tr>
                                <tr>
                                    <td>15</td>
                                    <td><code>member_education_level</code></td>
                                    <td>High School</td>
                                </tr>
                                <tr>
                                    <td>16</td>
                                    <td><code>member_profession</code></td>
                                    <td>Farmer</td>
                                </tr>
                                <tr>
                                    <td>17</td>
                                    <td><code>member_is_pwd</code></td>
                                    <td>Y or N</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Sample Data -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Sample CSV Data</h6>
                        </div>
                        <div class="card-body p-3">
                            <pre class="small mb-0" style="background: #f8f9fa; border-radius: 0.375rem; padding: 1rem; font-size: 0.875rem; max-height: 200px; overflow-y: auto;">head_first_name,head_middle_name,head_last_name,street,purok,barangay_id,contact_number,emergency_contact,member_first_name,member_middle_name,member_last_name,member_birth_date,member_sex,member_civil_status,member_education_level,member_profession,member_is_pwd
Juan,M,Dela Cruz,"123 Main St",Purok 1,1,09123456789,09987654321,Juan,M,Dela Cruz,15/05/1990,M,Married,"High School",Farmer,N
Maria,A,Santos,"456 Oak Ave",Purok 2,2,09234567890,09876543210,Maria,A,Santos,22/03/1988,F,Single,College,Teacher,N</pre>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <a href="{{ route('households.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-list me-2"></i>View Households
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-info w-100" onclick="downloadSample()">
                                <i class="fas fa-download me-2"></i>Download Sample CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadSample() {
        const sampleData = `head_first_name,head_middle_name,head_last_name,street,purok,barangay_id,contact_number,emergency_contact,member_first_name,member_middle_name,member_last_name,member_birth_date,member_sex,member_civil_status,member_education_level,member_profession,member_is_pwd
Juan,M,Dela Cruz,"123 Main St",Purok 1,1,09123456789,09987654321,Juan,M,Dela Cruz,15/05/1990,M,Married,"High School",Farmer,N
Maria,A,Santos,"456 Oak Ave",Purok 2,2,09234567890,09876543210,Maria,A,Santos,22/03/1988,F,Single,College,Teacher,N`;
        const blob = new Blob([sampleData], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'household_template.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    document.getElementById('csvForm').addEventListener('submit', function(e) {
        document.getElementById('uploadBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        document.getElementById('uploadBtn').disabled = true;
    });
</script>

@endsection

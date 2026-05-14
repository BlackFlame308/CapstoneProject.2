@extends('layouts.admin')

@section('page_title', 'Upload CSV')
@section('page_icon')
    <i class="fas fa-file-upload"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                <h6 style="margin: 0; font-weight: 600; color: #333;">
                    <i class="fas fa-file-csv"></i> Import Households from CSV
                </h6>
            </div>
            <div class="card-body" style="padding: 30px;">
                <form method="POST" action="{{ route('csv.upload.process') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="csv_file" class="form-label">CSV File <span style="color: red;">*</span></label>
                        <input type="file" class="form-control @error('csv_file') is-invalid @enderror"
                               id="csv_file" name="csv_file" accept=".csv,.txt" required>
                        @error('csv_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">Supported formats: .csv or .txt, up to 10MB.</small>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <a href="{{ route('admin.households.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload and Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

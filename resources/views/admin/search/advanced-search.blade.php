@extends('layouts.admin')

@section('page_title', 'Advanced Search')
@section('page_icon')
    <i class="fas fa-search"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <form action="{{ route('admin.search.search') }}" method="GET" class="mb-4">
                    <div class="mb-3">
                        <label for="q" class="form-label">
                            <i class="fas fa-search"></i> Search Query
                        </label>
                        <input type="text" class="form-control form-control-lg" id="q" name="q" 
                               placeholder="Search by name, code, contact number..."
                               value="{{ request('q') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="type" class="form-label">Search In</label>
                            <select class="form-select" id="type" name="type">
                                <option value="all" @if(request('type') == 'all') selected @endif>All</option>
                                <option value="households" @if(request('type') == 'households') selected @endif>Households</option>
                                <option value="members" @if(request('type') == 'members') selected @endif>Members</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="barangay_id" class="form-label">Barangay</label>
                            <select class="form-select" id="barangay_id" name="barangay_id">
                                <option value="">-- All --</option>
                                <!-- Barangays from database -->
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="gender_id" class="form-label">Gender</label>
                            <select class="form-select" id="gender_id" name="gender_id">
                                <option value="">-- All --</option>
                                <option value="1">Male</option>
                                <option value="2">Female</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="vulnerable_only" name="vulnerable_only" value="1"
                                       @if(request('vulnerable_only')) checked @endif>
                                <label class="form-check-label" for="vulnerable_only">
                                    Vulnerable Members Only
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('admin.search.form') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>

                <div class="alert alert-info">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Search Tips:</strong> 
                    Use partial names or codes. Apply filters to narrow results. Click any result to view details.
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

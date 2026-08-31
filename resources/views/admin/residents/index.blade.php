@extends('layouts.admin')

@section('page_title', 'Residents')
@section('page_icon')
    <i class="fas fa-users"></i>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 style="margin:0; font-weight:600; color:#333;">
                @if($selectedHouseholdId)
                    Residents of
                    <span style="color:#667eea;">
                        {{ $households->firstWhere('household_id', $selectedHouseholdId)?->household_name ?? 'Selected Household' }}
                    </span>
                    <span class="badge" style="background:#667eea; color:white; font-size:.8rem;">
                        {{ $residents->total() }} resident(s)
                    </span>
                @else
                    All Residents
                    <span class="badge" style="background:#667eea; color:white; font-size:.8rem;">
                        {{ $groupedResidents ? $groupedResidents->flatten()->count() : 0 }} total
                    </span>
                @endif
            </h5>
            <a href="{{ route('admin.households.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-home"></i> Choose Household
            </a>
        </div>
    </div>

    {{-- Household Filter --}}
    <div class="card mb-4" style="background:white; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); border:none;">
        <div class="card-body" style="padding:18px 20px;">
            <form method="GET" action="{{ route('admin.residents.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.85rem; color:#555;">
                        <i class="fas fa-filter me-1"></i> Filter by Household
                    </label>
                    <select name="household_id" id="household_filter" class="form-select">
                        <option value="">— Show all households (grouped) —</option>
                        @foreach($households as $hh)
                            <option value="{{ $hh->household_id }}"
                                {{ $selectedHouseholdId == $hh->household_id ? 'selected' : '' }}>
                                {{ $hh->household_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.residents.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================
         GROUPED VIEW — default, no filter selected
         Groups all residents under their household header.
         Uses actual DB relationship, not surname matching.
    ========================================================= --}}
    @if($groupedResidents !== null)

        @forelse($groupedResidents as $householdId => $members)
            @php $hh = $members->first()->household; @endphp

            {{-- Household Group Header --}}
            <div class="mt-4" style="
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 10px 10px 0 0;
                padding: 12px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            ">
                <div>
                    <div style="font-size:.68rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.1em; text-transform:uppercase; margin-bottom:2px;">
                        HOUSEHOLD
                    </div>
                    <h6 style="margin:0; font-weight:700; color:white; font-size:1rem;">
                        <i class="fas fa-home me-2" style="opacity:.8;"></i>
                        {{ $hh?->household_name ?? 'Unknown Household' }}
                        @if($hh?->household_code)
                            <span style="font-size:.8rem; font-weight:400; opacity:.75; margin-left:6px;">({{ $hh->household_code }})</span>
                        @endif
                    </h6>
                </div>
                <span class="badge" style="background:rgba(255,255,255,.2); color:white; font-size:.8rem; padding:6px 14px; border-radius:20px;">
                    {{ $members->count() }} {{ $members->count() === 1 ? 'resident' : 'residents' }}
                </span>
            </div>

            {{-- Members table for this household --}}
            <div class="card mb-0" style="border-radius:0 0 10px 10px; border:1px solid #ddd; border-top:none; overflow:hidden; margin-bottom:8px!important;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8f9fa;">
                            <tr style="border-bottom:2px solid #dee2e6;">
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Name</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Age</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Gender</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">PWD</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Senior</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Child</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Pregnant</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Adult</th>
                                <th style="padding:10px 15px; font-weight:600; color:#555; font-size:.85rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $resident)
                            <tr style="border-bottom:1px solid #f1f1f1;">
                                <td style="padding:10px 15px; vertical-align:middle;">
                                    <strong style="color:#333;">{{ $resident->full_name }}</strong>
                                </td>
                                <td style="padding:10px 15px; vertical-align:middle;">{{ $resident->age ?? 'N/A' }}</td>
                                <td style="padding:10px 15px; vertical-align:middle;">
                                    {{ $resident->gender ? ucfirst($resident->gender) : ($resident->sex ?? 'N/A') }}
                                </td>
                                <td style="padding:10px 15px; vertical-align:middle;">
                                    @if($resident->is_pwd)
                                        <span class="badge bg-warning text-dark">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td style="padding:10px 15px; vertical-align:middle;">
                                    @if($resident->is_senior)
                                        <span class="badge bg-info text-dark">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td style="padding:10px 15px; vertical-align:middle;">
                                    @if($resident->age !== null && $resident->age < 18)
                                        <span class="badge bg-danger">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td style="padding:10px 15px; vertical-align:middle;">
                                    @if($resident->is_pregnant)
                                        <span class="badge text-white" style="background-color:#e83e8c;">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td style="padding:10px 15px; vertical-align:middle;">
                                    @if($resident->age !== null && $resident->age >= 18 && $resident->age < 60)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td style="padding:10px 15px; vertical-align:middle; white-space:nowrap;">
                                    <a href="{{ route('admin.residents.edit', $resident) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()?->canDeleteHouseholds())
                                    <form action="{{ route('admin.residents.destroy', $resident) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete {{ addslashes($resident->full_name) }}?\nThis will NOT remove other household members.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-3x mb-3 d-block" style="color:#ccc;"></i>
                    <p class="text-muted mb-0">No residents found.</p>
                </div>
            </div>
        @endforelse

    {{-- ========================================================
         FILTERED VIEW — a specific household is selected
         Flat paginated table for the chosen household.
    ========================================================= --}}
    @else

        <div class="card" style="background:white; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); border:none;">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                            <th style="padding:12px 15px;">Name</th>
                            <th style="padding:12px 15px;">Household</th>
                            <th style="padding:12px 15px;">Age</th>
                            <th style="padding:12px 15px;">Gender</th>
                            <th style="padding:12px 15px;">PWD</th>
                            <th style="padding:12px 15px;">Senior</th>
                            <th style="padding:12px 15px;">Child</th>
                            <th style="padding:12px 15px;">Pregnant</th>
                            <th style="padding:12px 15px;">Adult</th>
                            <th style="padding:12px 15px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($residents as $resident)
                        <tr style="border-bottom:1px solid #f1f1f1;">
                            <td style="padding:12px 15px; vertical-align:middle;">
                                <strong>{{ $resident->full_name }}</strong>
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle;">
                                <span class="badge" style="background:#667eea; color:white;">
                                    {{ $resident->household?->household_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle;">{{ $resident->age ?? 'N/A' }}</td>
                            <td style="padding:12px 15px; vertical-align:middle;">
                                {{ $resident->gender ? ucfirst($resident->gender) : ($resident->sex ?? 'N/A') }}
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle;">
                                @if($resident->is_pwd)
                                    <span class="badge bg-warning text-dark">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle;">
                                @if($resident->is_senior)
                                    <span class="badge bg-info text-dark">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle;">
                                @if($resident->age !== null && $resident->age < 18)
                                    <span class="badge bg-danger">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle;">
                                @if($resident->is_pregnant)
                                    <span class="badge text-white" style="background-color:#e83e8c;">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle;">
                                @if($resident->age !== null && $resident->age >= 18 && $resident->age < 60)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td style="padding:12px 15px; vertical-align:middle; white-space:nowrap;">
                                <a href="{{ route('admin.residents.edit', $resident) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(auth()->user()?->canDeleteHouseholds())
                                <form action="{{ route('admin.residents.destroy', $resident) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete {{ addslashes($resident->full_name) }}?\nThis will NOT remove other household members.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-users fa-2x mb-2 d-block" style="color:#ccc;"></i>
                                No residents found for this household.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($residents && $residents->hasPages())
            <div class="card-footer residents-pagination">
                {{ $residents->links() }}
            </div>
            @endif
        </div>

    @endif

</div>
@endsection

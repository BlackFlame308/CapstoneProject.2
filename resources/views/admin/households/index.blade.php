@extends('layouts.admin')

@section('page_title', 'Household Management')
@section('page_icon')
    <i class="fas fa-home"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; font-weight: 600; color: #333;">
                All Households ({{ $households->total() }})
            </h5>
            <a href="{{ route('admin.households.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Household
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-body" style="padding: 20px;">
        <form method="GET" action="{{ route('admin.households.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by code or name..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="col-md-4">
                <select name="barangay_id" class="form-select">
                    <option value="">-- Select Barangay --</option>
                    @foreach($barangays as $barangay)
                        <option value="{{ $barangay->id }}"
                            @if(($filters['barangay_id'] ?? '') == $barangay->id) selected @endif>
                            {{ $barangay->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.households.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Households Table -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="table-responsive">
        @if($households->count() > 0)
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">Code</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Name</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Location</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Members</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Contact</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Temporary Password</th>
                        <th style="font-weight: 600; color: #333; padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($households as $household)
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; vertical-align: middle;">
                                <strong style="color: #333;">{{ $household->household_code }}</strong>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                {{ $household->household_name ?? 'N/A' }}
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <small style="color: #999;">
                                    {{ $household->address?->purok_sitio ?? 'No location' }}
                                </small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: #667eea; color: white;">
                                    {{ $household->members->count() }}
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <small style="color: #999;">{{ $household->contact_number ?? 'N/A' }}</small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                @if($household->user)
                                    @if($household->user->temp_password)
                                        <code style="color: #dc3545; font-weight: 700; font-size: 14px;">{{ $household->user->temp_password }}</code>
                                    @else
                                        <span class="text-success" style="font-size: 13px;"><i class="fas fa-check-circle"></i> Changed</span>
                                    @endif
                                @else
                                    <span class="text-muted" style="font-size: 13px;"><i class="fas fa-user-slash"></i> No Account</span>
                                @endif
                            </td>
                            <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="{{ route('admin.households.show', $household) }}"
                                       class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.households.edit', $household) }}"
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()->role?->name && in_array(strtolower(auth()->user()->role->name), ['head', 'captain']))
                                        <form action="{{ route('admin.households.destroy', $household) }}"
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 60px 20px; text-align: center;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h5 style="color: #999; margin-bottom: 10px;">No households found</h5>
                <p style="color: #bbb; margin-bottom: 20px;">Create your first household to get started.</p>
                <a href="{{ route('admin.households.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Household
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $households->links('pagination::bootstrap-5') }}
</div>

@endsection

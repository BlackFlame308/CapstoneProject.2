@extends('layouts.admin')

@section('page_title', 'Vulnerable Groups Management')
@section('page_icon')
    <i class="fas fa-heart-broken"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; font-weight: 600; color: #333;">
                Vulnerable Groups ({{ $groups->total() }})
            </h5>
            <a href="{{ route('admin.vulnerable-groups.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Vulnerable Group
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-body" style="padding: 20px;">
        <form method="GET" action="{{ route('admin.vulnerable-groups.index') }}" class="row g-3">
            <div class="col-md-9">
                <input type="text" name="search" class="form-control" placeholder="Search by name or key..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="col-md-3">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.vulnerable-groups.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Groups Table -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="table-responsive">
        @if($groups->count() > 0)
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">Key</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Label</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Member Count</th>
                        <th style="font-weight: 600; color: #333; padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; vertical-align: middle;">
                                <code style="background: #f1f1f1; padding: 4px 8px; border-radius: 4px;">{{ $group->vulnerable_group_key }}</code>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <strong style="color: #333;">{{ $group->vulnerable_group_label }}</strong>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: #e7f3ff; color: #0066cc;">
                                    {{ $group->members()->count() }} members
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="{{ route('admin.vulnerable-groups.edit', $group) }}"
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.vulnerable-groups.destroy', $group) }}"
                                          method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure? This cannot be undone.')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 60px 20px; text-align: center;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h5 style="color: #999; margin-bottom: 10px;">No vulnerable groups found</h5>
                <p style="color: #bbb; margin-bottom: 20px;">Create your first vulnerable group to get started.</p>
                <a href="{{ route('admin.vulnerable-groups.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Vulnerable Group
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $groups->links('pagination::bootstrap-5') }}
</div>

@endsection

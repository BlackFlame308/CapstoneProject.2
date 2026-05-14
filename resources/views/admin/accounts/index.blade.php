@extends('layouts.admin')

@section('page_title', 'Account Management')
@section('page_icon')
    <i class="fas fa-users"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; font-weight: 600; color: #333;">
                All User Accounts ({{ $users->total() }})
            </h5>
            <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-body" style="padding: 20px;">
        <form method="GET" action="{{ route('admin.accounts.index') }}" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or username..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="col-md-4">
                <select name="role" class="form-select">
                    <option value="">-- All Roles --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}"
                            @if(($filters['role'] ?? '') == $role->name) selected @endif>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Accounts Table -->
<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="table-responsive">
        @if($users->count() > 0)
            <table class="table" style="margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="font-weight: 600; color: #333; padding: 15px;">Name</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Username</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Email</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Role</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Household</th>
                        <th style="font-weight: 600; color: #333; padding: 15px;">Status</th>
                        <th style="font-weight: 600; color: #333; padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr style="border-bottom: 1px solid #f1f1f1;">
                            <td style="padding: 15px; vertical-align: middle;">
                                <strong style="color: #333;">{{ $user->name }}</strong>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <small style="color: #999;">{{ $user->username }}</small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <small style="color: #999;">{{ $user->email }}</small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <span class="badge" style="background-color: #667eea; color: white;">
                                    {{ ucfirst($user->role ?? 'N/A') }}
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <small style="color: #999;">
                                    {{ $user->household?->household_code ?? 'None' }}
                                </small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                @if($user->is_active)
                                    <span class="badge" style="background-color: #d4edda; color: #155724;">Active</span>
                                @else
                                    <span class="badge" style="background-color: #f8d7da; color: #721c24;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="{{ route('admin.accounts.edit', $user) }}"
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.accounts.destroy', $user) }}"
                                          method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure?')" title="Delete">
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
                <h5 style="color: #999; margin-bottom: 10px;">No accounts found</h5>
                <p style="color: #bbb; margin-bottom: 20px;">Create your first account to get started.</p>
                <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $users->links('pagination::bootstrap-5') }}
</div>

@endsection

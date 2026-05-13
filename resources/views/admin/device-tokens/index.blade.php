@extends('layouts.admin')

@section('page_title', 'Device Tracking')
@section('page_icon')
    <i class="fas fa-mobile-alt"></i>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h5 style="margin: 0; font-weight: 600; color: #333;">
            Active Devices ({{ $tokens->total() }})
        </h5>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-body" style="padding: 20px;">
        <form method="GET" action="{{ route('admin.device-tokens.index') }}" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by household code or device ID..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- All Status --</option>
                    <option value="active" @if($filters['status'] == 'active') selected @endif>Active (Last 1h)</option>
                    <option value="inactive" @if($filters['status'] == 'inactive') selected @endif>Inactive</option>
                </select>
            </div>

            <div class="col-md-3">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.device-tokens.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Devices Grid -->
<div class="row">
    @forelse($tokens as $token)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none; overflow: hidden;">
                <div style="padding: 20px; border-bottom: 2px solid {{ $token->isActive() ? '#d4edda' : '#f8d7da' }};">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div>
                            <h6 style="margin: 0 0 5px 0; color: #333; font-weight: 600;">
                                {{ $token->household->household_code }}
                            </h6>
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                {{ $token->household->household_name }}
                            </p>
                        </div>
                        <span class="badge" style="background-color: {{ $token->isActive() ? '#d4edda' : '#f8d7da' }}; color: {{ $token->isActive() ? '#155724' : '#721c24' }};">
                            {{ $token->isActive() ? '🟢 Active' : '🔴 Inactive' }}
                        </span>
                    </div>

                    <div style="font-size: 12px; color: #666;">
                        <p style="margin: 8px 0;">
                            <i class="fas fa-phone-mobile"></i> <code>{{ substr($token->player_id, 0, 8) }}...</code>
                        </p>
                        <p style="margin: 8px 0;">
                            <i class="fas fa-clock"></i> {{ $token->logged_at ? $token->logged_at->diffForHumans() : 'Never' }}
                        </p>
                    </div>
                </div>

                <div style="padding: 15px; background-color: #f8f9fa;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <!-- Battery -->
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 4px;">Battery</small>
                            @if($token->battery_level)
                                <div style="background: #e9ecef; height: 6px; border-radius: 3px; overflow: hidden;">
                                    <div style="background: {{ $token->battery_level >= 50 ? '#28a745' : ($token->battery_level >= 20 ? '#ffc107' : '#dc3545') }}; height: 100%; width: {{ $token->battery_level }}%;"></div>
                                </div>
                                <small style="color: #666; display: block; margin-top: 2px;">{{ $token->battery_level }}%</small>
                            @else
                                <small style="color: #999;">Unknown</small>
                            @endif
                        </div>

                        <!-- Signal -->
                        <div>
                            <small style="color: #999; display: block; margin-bottom: 4px;">Signal</small>
                            @if($token->signal_strength)
                                <div style="background: #e9ecef; height: 6px; border-radius: 3px; overflow: hidden;">
                                    <div style="background: {{ $token->signal_strength >= 60 ? '#28a745' : ($token->signal_strength >= 40 ? '#ffc107' : '#dc3545') }}; height: 100%; width: {{ $token->signal_strength }}%;"></div>
                                </div>
                                <small style="color: #666; display: block; margin-top: 2px;">{{ $token->signal_strength }}%</small>
                            @else
                                <small style="color: #999;">Unknown</small>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.device-tokens.show', $token) }}" class="btn btn-info btn-sm flex-grow-1">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <form action="{{ route('admin.device-tokens.destroy', $token) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this device token?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div style="padding: 60px 20px; text-align: center;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h5 style="color: #999; margin-bottom: 10px;">No devices found</h5>
                <p style="color: #bbb;">Device tokens appear when households connect the mobile app</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($tokens->lastPage() > 1)
<div class="mt-4">
    {{ $tokens->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection

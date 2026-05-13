@extends('layouts.admin')

@section('page_title', 'Device Details')
@section('page_icon')
    <i class="fas fa-mobile-alt"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <a href="{{ route('admin.device-tokens.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back to Devices
        </a>

        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <!-- Household Info -->
                <div class="mb-4">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">
                        <i class="fas fa-home"></i> Household Information
                    </h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <p style="margin: 5px 0;">
                            <strong>Code:</strong> {{ $token->household->household_code }}
                        </p>
                        <p style="margin: 5px 0;">
                            <strong>Name:</strong> {{ $token->household->household_name }}
                        </p>
                        <p style="margin: 5px 0;">
                            <strong>Contact:</strong> {{ $token->household->contact_number ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <hr>

                <!-- Device Info -->
                <div class="mb-4">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">
                        <i class="fas fa-mobile-alt"></i> Device Information
                    </h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <p style="margin: 5px 0;">
                            <strong>Device ID:</strong> <code>{{ $token->player_id }}</code>
                        </p>
                        <p style="margin: 5px 0;">
                            <strong>Status:</strong>
                            <span class="badge" style="background-color: {{ $token->isActive() ? '#d4edda' : '#f8d7da' }}; color: {{ $token->isActive() ? '#155724' : '#721c24' }};">
                                {{ $token->isActive() ? '🟢 Active' : '🔴 Inactive' }}
                            </span>
                        </p>
                        <p style="margin: 5px 0;">
                            <strong>Last Active:</strong> {{ $token->logged_at ? $token->logged_at->format('M d, Y H:i:s') : 'Never' }}
                        </p>
                    </div>
                </div>

                <hr>

                <!-- Device Health -->
                <div class="mb-4">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">
                        <i class="fas fa-heartbeat"></i> Device Health
                    </h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <div class="mb-3">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span><strong>Battery Level</strong></span>
                                <span style="color: #999;">{{ $token->battery_level ?? 'Unknown' }}%</span>
                            </div>
                            @if($token->battery_level)
                                <div style="background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: {{ $token->battery_level >= 50 ? '#28a745' : ($token->battery_level >= 20 ? '#ffc107' : '#dc3545') }}; height: 100%; width: {{ $token->battery_level }}%;"></div>
                                </div>
                                <small style="color: #666; margin-top: 5px; display: block;">
                                    Status: <strong>{{ ucfirst($token->getBatteryStatus()) }}</strong>
                                </small>
                            @else
                                <p style="color: #999; margin: 0;">No data available</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span><strong>Signal Strength</strong></span>
                                <span style="color: #999;">{{ $token->signal_strength ?? 'Unknown' }}%</span>
                            </div>
                            @if($token->signal_strength)
                                <div style="background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: {{ $token->signal_strength >= 60 ? '#28a745' : ($token->signal_strength >= 40 ? '#ffc107' : '#dc3545') }}; height: 100%; width: {{ $token->signal_strength }}%;"></div>
                                </div>
                                <small style="color: #666; margin-top: 5px; display: block;">
                                    Status: <strong>{{ ucfirst($token->getSignalStatus()) }}</strong>
                                </small>
                            @else
                                <p style="color: #999; margin: 0;">No data available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Actions -->
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="{{ route('admin.device-tokens.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <form action="{{ route('admin.device-tokens.destroy', $token) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this device token?')">
                            <i class="fas fa-trash"></i> Delete Token
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

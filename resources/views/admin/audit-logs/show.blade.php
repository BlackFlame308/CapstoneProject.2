@extends('layouts.admin')

@section('page_title', 'Audit Log Details')
@section('page_icon')
    <i class="fas fa-history"></i>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back to Logs
        </a>

        <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
            <div class="card-body" style="padding: 30px;">

                <!-- Header Info -->
                <div class="mb-4">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">
                        <i class="fas fa-info-circle"></i> Change Details
                    </h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <p style="margin: 8px 0;">
                            <strong>User:</strong> {{ $log->user_id }}
                        </p>
                        <p style="margin: 8px 0;">
                            <strong>Action:</strong> 
                            <span class="badge" style="background-color: #e7f3ff; color: #0066cc;">
                                {{ ucfirst($log->action) }}
                            </span>
                        </p>
                        <p style="margin: 8px 0;">
                            <strong>Model:</strong> <code>{{ $log->model }}</code>
                        </p>
                        <p style="margin: 8px 0;">
                            <strong>Timestamp:</strong> {{ $log->created_at->format('M d, Y H:i:s') }}
                        </p>
                    </div>
                </div>

                <hr>

                <!-- Changes -->
                <div>
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">
                        <i class="fas fa-sync"></i> Changes Made
                    </h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; white-space: pre-wrap; word-break: break-word;">
                        @if($log->changes)
                            {{ json_encode(json_decode($log->changes), JSON_PRETTY_PRINT) }}
                        @else
                            No changes recorded
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Actions -->
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

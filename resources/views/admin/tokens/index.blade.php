@extends('layouts.admin')

@section('page_title', 'API Token Management')
@section('page_icon')
    <i class="fas fa-key"></i>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Success Alert with Plain Text Token -->
    @if (session()->has('plain_text_token'))
        <div class="card mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 12px; border: 1px solid #10b981; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);">
            <div class="card-body" style="padding: 25px; color: white;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="width: 45px; height: 45px; border-radius: 50%; background-color: rgba(16, 185, 129, 0.2); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; font-weight: 700; color: #10b981;">API Token Generated Successfully!</h5>
                        <p style="margin: 0; opacity: 0.8; font-size: 13.5px; margin-top: 2px;">
                            For security reasons, this token will only be shown once. Please copy it now and save it securely.
                        </p>
                    </div>
                </div>

                <div style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; border: 1px dashed rgba(255,255,255,0.15); padding: 15px; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <span id="api-token-text" style="font-family: monospace; font-size: 16px; font-weight: 600; color: #34d399; letter-spacing: 0.5px; overflow-wrap: anywhere; word-break: break-all;">
                        {{ session('plain_text_token') }}
                    </span>
                    <button type="button" onclick="copyApiToken()" id="copy-token-btn" class="btn btn-success" style="padding: 8px 16px; font-size: 13px; font-weight: 600; min-width: 90px; border-radius: 6px;">
                        <i class="fas fa-copy me-1"></i> Copy
                    </button>
                </div>
            </div>
        </div>

        <script>
            function copyApiToken() {
                var tokenText = document.getElementById('api-token-text').innerText.trim();
                navigator.clipboard.writeText(tokenText).then(function() {
                    var btn = document.getElementById('copy-token-btn');
                    btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-light');
                    setTimeout(function() { 
                        btn.innerHTML = '<i class="fas fa-copy me-1"></i> Copy'; 
                        btn.classList.remove('btn-light');
                        btn.classList.add('btn-success');
                    }, 3000);
                });
            }
        </script>
    @endif

    <div class="row">
        <!-- Tokens Table List -->
        <div class="col-lg-8 mb-4">
            <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none; height: 100%;">
                <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                    <h6 style="margin: 0; font-weight: 600; color: #333;">
                        <i class="fas fa-list-ul me-2 text-primary"></i>Active API Access Tokens
                    </h6>
                </div>
                <div class="card-body" style="padding: 20px;">
                    @if($tokens->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle" style="box-shadow: none; border-radius: 0; margin: 0;">
                                <thead>
                                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                        <th style="padding: 12px; font-weight: 600; color: #555;">Token Details</th>
                                        <th style="padding: 12px; font-weight: 600; color: #555;">Authorized As</th>
                                        <th style="padding: 12px; font-weight: 600; color: #555;">Last Used</th>
                                        <th style="padding: 12px; font-weight: 600; color: #555; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tokens as $token)
                                        <tr style="border-bottom: 1px solid #f1f1f1;">
                                            <td style="padding: 15px;">
                                                <div style="font-weight: 600; color: #333;">{{ $token->name }}</div>
                                                <div style="font-size: 11px; color: #999; margin-top: 4px;">
                                                    Created: {{ \Carbon\Carbon::parse($token->created_at)->format('M d, Y h:i A') }}
                                                </div>
                                            </td>
                                            <td style="padding: 15px;">
                                                <div style="color: #444; font-weight: 500;">{{ $token->user_name }}</div>
                                                <small class="text-muted" style="font-size: 11.5px;">{{ $token->user_email }}</small>
                                            </td>
                                            <td style="padding: 15px; color: #666; font-size: 13.5px;">
                                                @if($token->last_used_at)
                                                    <i class="far fa-clock text-info me-1"></i>
                                                    {{ \Carbon\Carbon::parse($token->last_used_at)->diffForHumans() }}
                                                @else
                                                    <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 8px; border-radius: 4px;">Never</span>
                                                @endif
                                            </td>
                                            <td style="padding: 15px; text-align: center;">
                                                <form action="{{ route('admin.tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke this API token? Any application currently using this key will immediately lose access to the system.');" style="margin: 0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius: 6px; padding: 5px 12px;">
                                                        <i class="fas fa-ban me-1"></i> Revoke
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 60px 20px; color: #999;">
                            <i class="fas fa-shield-alt" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                            <h5 style="color: #888;">No API Tokens Found</h5>
                            <p style="font-size: 13.5px; color: #bbb; max-width: 400px; margin: 0 auto;">
                                You have not generated any API keys. Create a token using the generator card to connect external subsystems or mobile clients.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Token Generator Form -->
        <div class="col-lg-4 mb-4">
            <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
                <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 20px;">
                    <h6 style="margin: 0; font-weight: 600; color: #333;">
                        <i class="fas fa-plus-circle me-2 text-success"></i>Generate API Token
                    </h6>
                </div>
                <div class="card-body" style="padding: 25px;">
                    <form action="{{ route('admin.tokens.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="token_name" class="form-label">Token Label / Name</label>
                            <input type="text" name="token_name" id="token_name" class="form-control" placeholder="e.g. EvaTrack Subsystem" required>
                            <small class="text-muted" style="display: block; margin-top: 6px; font-size: 11px; line-height: 1.4;">
                                Give the token a descriptive name representing the application or module that will use it.
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="user_id" class="form-label">Authorized Account</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="" disabled selected>-- Choose User --</option>
                                @foreach($users as $usr)
                                    <option value="{{ $usr->user_id }}">
                                        {{ $usr->name }} ({{ $usr->role->name ?? 'User' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="display: block; margin-top: 6px; font-size: 11px; line-height: 1.4;">
                                Select the system user whose roles and permissions will be inherited by the API token.
                            </small>
                        </div>

                        <div class="mb-3">
                            <div style="background-color: #fcf8e3; border: 1px solid #fbeed5; border-radius: 8px; padding: 12px; color: #c09853; font-size: 11.5px; line-height: 1.4;">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <strong>Important:</strong> Generated tokens grant direct programmatic access to the database. Keep them secure.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" style="padding: 12px; font-weight: 600;">
                            <i class="fas fa-key me-2"></i> Generate Key
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

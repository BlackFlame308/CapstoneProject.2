@extends('layouts.admin')

@section('title', 'Settings - SafeTrack Admin')
@section('page_title', 'Settings')
@section('page_icon')
    <i class="fas fa-cog"></i>
@endsection

@push('styles')
<style>
    /* ── Settings page layout ── */
    .settings-nav {
        display: flex;
        gap: 6px;
        border-bottom: 2px solid rgba(0,0,0,0.06);
        padding-bottom: 0;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .settings-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
        font-size: 13.5px;
        font-weight: 600;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: -2px;
    }

    .settings-tab-btn:hover {
        color: var(--text-main);
        background: rgba(0,0,0,0.03);
    }

    .settings-tab-btn.active {
        color: #1f3042;
        border-bottom-color: #1f3042;
        background: rgba(31,48,66,0.05);
    }

    .settings-tab-btn i {
        font-size: 13px;
    }

    .settings-panel {
        display: none;
    }

    .settings-panel.active {
        display: block;
        animation: fadeIn 0.25s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Password strength meter ── */
    .strength-bar-wrap {
        height: 6px;
        border-radius: 99px;
        background: #e2e8f0;
        margin-top: 8px;
        overflow: hidden;
    }

    .strength-bar {
        height: 100%;
        width: 0%;
        border-radius: 99px;
        transition: width 0.3s ease, background 0.3s ease;
    }

    .strength-label {
        font-size: 11px;
        margin-top: 4px;
        font-weight: 600;
    }

    /* ── Settings card wrapper ── */
    .settings-card {
        background: #fff;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .settings-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .settings-card-header-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .settings-card-header h6 {
        font-size: 15px;
        font-weight: 700;
        margin: 0;
        color: var(--text-main);
    }

    .settings-card-header p {
        font-size: 12px;
        color: var(--text-muted);
        margin: 2px 0 0;
    }

    .settings-card-body {
        padding: 28px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ────── Tabs nav ────── --}}
    <div class="settings-nav" id="settings-nav">
        <button class="settings-tab-btn active" id="tab-btn-password" onclick="showTab('password')">
            <i class="fas fa-lock"></i> Change Password
        </button>

        @if($canDelete)
        <button class="settings-tab-btn" id="tab-btn-tokens" onclick="showTab('tokens')">
            <i class="fas fa-key"></i> API Token Management
        </button>
        @endif
    </div>

    {{-- ══════════════════════════════════
         PANEL 1 — Change Password
    ══════════════════════════════════ --}}
    <div class="settings-panel active" id="panel-password">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-header-icon" style="background:#f0f4ff; color:#1f3042;">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h6>Change Password</h6>
                            <p>Update your account password. Use a strong, unique password.</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST" action="{{ route('admin.settings.update-password') }}" id="change-password-form">
                            @csrf

                            {{-- Current Password --}}
                            <div class="mb-4">
                                <label for="current_password" class="form-label">Current Password</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('current_password') is-invalid @enderror"
                                           id="current_password"
                                           name="current_password"
                                           placeholder="Enter your current password"
                                           required autofocus>
                                    <button class="btn btn-outline-secondary toggle-pwd" type="button" tabindex="-1"
                                            data-target="current_password" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- New Password --}}
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           placeholder="Minimum 8 chars, uppercase, number"
                                           oninput="checkStrength(this.value)"
                                           required>
                                    <button class="btn btn-outline-secondary toggle-pwd" type="button" tabindex="-1"
                                            data-target="password" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                {{-- Strength meter --}}
                                <div class="strength-bar-wrap mt-2">
                                    <div class="strength-bar" id="strength-bar"></div>
                                </div>
                                <div class="strength-label text-muted" id="strength-label">Enter a password</div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Confirm New Password --}}
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           placeholder="Re-enter new password"
                                           required>
                                    <button class="btn btn-outline-secondary toggle-pwd" type="button" tabindex="-1"
                                            data-target="password_confirmation" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Requirements hint --}}
                            <div class="alert alert-info" style="font-size:12.5px; padding: 10px 14px;">
                                <i class="fas fa-info-circle me-1"></i>
                                Password must be at least <strong>8 characters</strong> and include
                                <strong>uppercase &amp; lowercase letters</strong> and at least one <strong>number</strong>.
                            </div>

                            <button type="submit" class="btn btn-primary w-100" style="padding: 12px; margin-top: 4px;">
                                <i class="fas fa-save me-2"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════
         PANEL 2 — API Token Management
    ══════════════════════════════════ --}}
    @if($canDelete)
    <div class="settings-panel" id="panel-tokens">

        {{-- Show newly-generated token --}}
        @if (session()->has('plain_text_token'))
            <div class="card mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                         border-radius: 12px; border: 1px solid #10b981;
                         box-shadow: 0 10px 25px -5px rgba(16,185,129,0.3);">
                <div class="card-body" style="padding: 25px; color: white;">
                    <div style="display:flex; align-items:center; gap:15px; margin-bottom:15px;">
                        <div style="width:45px; height:45px; border-radius:50%;
                                    background-color:rgba(16,185,129,0.2);
                                    display:flex; align-items:center; justify-content:center;
                                    color:#10b981; font-size:20px;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5 style="margin:0; font-weight:700; color:#10b981;">API Token Generated Successfully!</h5>
                            <p style="margin:0; opacity:.8; font-size:13.5px; margin-top:2px;">
                                For security reasons, this token will only be shown once. Copy it now and save it securely.
                            </p>
                        </div>
                    </div>

                    <div style="background:rgba(255,255,255,0.08); border-radius:8px;
                                border:1px dashed rgba(255,255,255,0.15); padding:15px;
                                display:flex; justify-content:space-between; align-items:center; gap:15px;">
                        <span id="api-token-text"
                              style="font-family:monospace; font-size:16px; font-weight:600;
                                     color:#34d399; letter-spacing:.5px;
                                     overflow-wrap:anywhere; word-break:break-all;">
                            {{ session('plain_text_token') }}
                        </span>
                        <button type="button" onclick="copyApiToken()" id="copy-token-btn"
                                class="btn btn-success"
                                style="padding:8px 16px; font-size:13px; font-weight:600; min-width:90px; border-radius:6px;">
                            <i class="fas fa-copy me-1"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            {{-- Active Tokens Table --}}
            <div class="col-lg-8 mb-4">
                <div class="settings-card" style="height:100%;">
                    <div class="settings-card-header">
                        <div class="settings-card-header-icon" style="background:#eff6ff; color:#1d4ed8;">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <div>
                            <h6>Active API Access Tokens</h6>
                            <p>Manage tokens that grant programmatic access to the system.</p>
                        </div>
                    </div>
                    <div class="settings-card-body" style="padding: 0;">
                        @if($tokens->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-middle" style="box-shadow:none; border-radius:0; margin:0;">
                                    <thead>
                                        <tr>
                                            <th>Token Details</th>
                                            <th>Authorized As</th>
                                            <th>Last Used</th>
                                            <th style="text-align:center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tokens as $token)
                                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                                <td style="padding:15px;">
                                                    <div style="font-weight:600; color:#333;">{{ $token->name }}</div>
                                                    <div style="font-size:11px; color:#999; margin-top:4px;">
                                                        Created: {{ \Carbon\Carbon::parse($token->created_at)->format('M d, Y h:i A') }}
                                                    </div>
                                                </td>
                                                <td style="padding:15px;">
                                                    <div style="color:#444; font-weight:500;">{{ $token->user_name }}</div>
                                                    <small class="text-muted" style="font-size:11.5px;">{{ $token->user_email }}</small>
                                                </td>
                                                <td style="padding:15px; color:#666; font-size:13.5px;">
                                                    @if($token->last_used_at)
                                                        <i class="far fa-clock text-info me-1"></i>
                                                        {{ \Carbon\Carbon::parse($token->last_used_at)->diffForHumans() }}
                                                    @else
                                                        <span class="badge bg-secondary" style="font-size:10px; padding:4px 8px; border-radius:4px;">Never</span>
                                                    @endif
                                                </td>
                                                <td style="padding:15px; text-align:center;">
                                                    <form action="{{ route('admin.settings.destroy-token', $token->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Are you sure you want to revoke this API token? Any application currently using this key will immediately lose access.');"
                                                          style="margin:0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                style="border-radius:6px; padding:5px 12px;">
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
                            <div style="text-align:center; padding:60px 20px; color:#999;">
                                <i class="fas fa-shield-alt" style="font-size:48px; color:#ddd; margin-bottom:20px; display:block;"></i>
                                <h5 style="color:#888;">No API Tokens Found</h5>
                                <p style="font-size:13.5px; color:#bbb; max-width:400px; margin:0 auto;">
                                    You have not generated any API keys. Use the generator on the right to connect external subsystems or mobile clients.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Token Generator --}}
            <div class="col-lg-4 mb-4">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-header-icon" style="background:#f0fdf4; color:#15803d;">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <h6>Generate API Token</h6>
                            <p>Create a new access token for an authorized user.</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <form action="{{ route('admin.settings.store-token') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="token_name" class="form-label">Token Label / Name</label>
                                <input type="text" name="token_name" id="token_name"
                                       class="form-control"
                                       placeholder="e.g. EvaTrack Subsystem" required>
                                <small class="text-muted" style="display:block; margin-top:6px; font-size:11px; line-height:1.4;">
                                    Give the token a descriptive name representing the application that will use it.
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
                                <small class="text-muted" style="display:block; margin-top:6px; font-size:11px; line-height:1.4;">
                                    Select the user whose roles and permissions will be inherited by this token.
                                </small>
                            </div>

                            <div class="mb-4">
                                <div style="background:#fcf8e3; border:1px solid #fbeed5; border-radius:8px;
                                            padding:12px; color:#c09853; font-size:11.5px; line-height:1.4;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Important:</strong> Generated tokens grant direct programmatic access. Keep them secure.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" style="padding:12px; font-weight:600;">
                                <i class="fas fa-key me-2"></i> Generate Key
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    /* ─── Tab switching ─── */
    function showTab(tab) {
        // Hide all panels
        document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.settings-tab-btn').forEach(b => b.classList.remove('active'));

        // Show selected
        const panel = document.getElementById('panel-' + tab);
        const btn   = document.getElementById('tab-btn-' + tab);
        if (panel) panel.classList.add('active');
        if (btn)   btn.classList.add('active');

        // Persist in URL hash without scrolling
        history.replaceState(null, '', '#' + tab);
    }

    /* ─── Restore tab from URL hash on load ─── */
    (function () {
        const hash = location.hash.replace('#', '');
        if (hash && document.getElementById('panel-' + hash)) {
            showTab(hash);
        }
    })();

    /* ─── Password visibility toggle ─── */
    document.querySelectorAll('.toggle-pwd').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const input    = document.getElementById(targetId);
            const icon     = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    /* ─── Password strength meter ─── */
    function checkStrength(val) {
        const bar   = document.getElementById('strength-bar');
        const label = document.getElementById('strength-label');

        let score = 0;
        if (val.length >= 8)                score++;
        if (/[A-Z]/.test(val))              score++;
        if (/[a-z]/.test(val))              score++;
        if (/[0-9]/.test(val))              score++;
        if (/[^A-Za-z0-9]/.test(val))       score++;

        const map = {
            0: { w: '0%',   c: '#ef4444', t: 'Enter a password' },
            1: { w: '20%',  c: '#ef4444', t: 'Very Weak' },
            2: { w: '40%',  c: '#f59e0b', t: 'Weak' },
            3: { w: '60%',  c: '#eab308', t: 'Fair' },
            4: { w: '80%',  c: '#22c55e', t: 'Strong' },
            5: { w: '100%', c: '#10b981', t: 'Very Strong' },
        };

        const entry = map[score] || map[0];
        bar.style.width      = entry.w;
        bar.style.background = entry.c;
        label.textContent    = entry.t;
        label.style.color    = entry.c;
    }

    /* ─── Copy API token ─── */
    function copyApiToken() {
        const tokenText = document.getElementById('api-token-text').innerText.trim();
        navigator.clipboard.writeText(tokenText).then(function () {
            const btn = document.getElementById('copy-token-btn');
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
            btn.classList.replace('btn-success', 'btn-light');
            setTimeout(function () {
                btn.innerHTML = '<i class="fas fa-copy me-1"></i> Copy';
                btn.classList.replace('btn-light', 'btn-success');
            }, 3000);
        });
    }

    /* ─── Auto-open tokens tab if redirected from token action ─── */
    @if(session()->has('plain_text_token'))
        showTab('tokens');
    @endif
</script>
@endpush

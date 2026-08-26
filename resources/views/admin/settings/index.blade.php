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

    {{-- Change Password Form --}}
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

</div>
@endsection

@push('scripts')
<script>
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
</script>
@endpush

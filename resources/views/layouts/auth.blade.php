<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SafeTrack')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --navy:       #1a3a5c;
            --navy-dark:  #0d2338;
            --navy-mid:   #1e4a72;
            --teal:       #1db87e;
            --teal-dark:  #159962;
            --teal-light: #22d492;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(160deg, var(--navy-dark) 0%, #0f2d4a 35%, #163550 65%, var(--navy-dark) 100%);
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
        }

        /* ── Background orbs ── */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .bg-orb-1 {
            width: 550px; height: 550px;
            top: -200px; left: -180px;
            background: radial-gradient(circle, rgba(29,184,126,0.08) 0%, transparent 70%);
            animation: orbDrift1 20s ease-in-out infinite;
            will-change: transform;
        }
        .bg-orb-2 {
            width: 420px; height: 420px;
            bottom: -120px; right: -120px;
            background: radial-gradient(circle, rgba(30,74,114,0.2) 0%, transparent 70%);
            animation: orbDrift2 24s ease-in-out infinite;
            will-change: transform;
        }
        @keyframes orbDrift1 {
            0%,100% { transform: translate(0,0); }
            50%      { transform: translate(28px,-24px); }
        }
        @keyframes orbDrift2 {
            0%,100% { transform: translate(0,0); }
            50%      { transform: translate(-22px,20px); }
        }

        /* ── Star dots ── */
        .stars { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .star {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.45);
            animation: twinkle linear infinite;
            will-change: opacity;
        }
        @keyframes twinkle {
            0%,100% { opacity: 0.12; }
            50%      { opacity: 0.7; }
        }

        /* ── Auth shell ── */
        .auth-shell {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        /* ── Card — professional light blue-gray, clearly visible on dark bg ── */
        .auth-card {
            width: min(100%, 480px);
            background: #eef3fa;
            border-radius: 20px;
            border: none;
            box-shadow:
                0 8px 48px rgba(0,0,0,0.45),
                0 1px 0 rgba(255,255,255,0.7) inset;
            overflow: hidden;
            animation: cardIn 0.5s ease-out both;
            will-change: transform, opacity;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Top accent bar using brand teal */
        .auth-card-top {
            height: 4px;
            background: linear-gradient(90deg,
                var(--navy-mid) 0%,
                var(--teal) 40%,
                var(--teal-light) 70%,
                var(--teal) 100%);
            background-size: 300% 100%;
            animation: shimmer 5s linear infinite;
        }
        @keyframes shimmer {
            0%   { background-position: 0% 0%; }
            100% { background-position: 300% 0%; }
        }

        /* ── Header ── */
        .auth-header {
            padding: 32px 32px 20px;
            text-align: center;
        }

        /* Logo: white background so the SafeTrack logo is fully visible */
        .auth-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 96px; height: 96px;
            border-radius: 20px;
            background: #ffffff;
            border: 3px solid rgba(29,184,126,0.35);
            margin-bottom: 18px;
            box-shadow:
                0 0 0 6px rgba(29,184,126,0.08),
                0 8px 32px rgba(0,0,0,0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .auth-logo-wrap:hover {
            transform: scale(1.04);
            box-shadow:
                0 0 0 8px rgba(29,184,126,0.15),
                0 12px 40px rgba(0,0,0,0.35);
        }
        .auth-logo-wrap img {
            width: 72px; height: 72px;
            object-fit: contain;
        }

        .auth-header h1 {
            margin: 0 0 6px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.4px;
        }
        /* On white card: 'Safe' is navy, 'Track' is teal */
        .auth-header h1 .brand-safe { color: var(--navy); }
        .auth-header h1 .brand-track {
            background: linear-gradient(90deg, var(--teal), var(--teal-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .auth-header p {
            margin: 0;
            color: #6b7a99;
            font-size: 13px;
            font-weight: 400;
        }

        /* ── Body ── */
        .auth-body {
            padding: 6px 32px 30px;
        }

        /* ── Labels ── */
        .form-label {
            color: #1e3a5c;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        /* ── Inputs — white so they pop against the blue-gray card ── */
        .form-control,
        .form-select {
            background: #ffffff;
            border: 1.5px solid #ccd8ea;
            border-radius: 10px;
            color: #111827;
            padding: 11px 14px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .form-control::placeholder { color: #a0aec0; }
        .form-control:focus,
        .form-select:focus {
            background: #ffffff;
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(29,184,126,0.15);
            color: #111827;
            outline: none;
        }
        .form-select option { background: #ffffff; color: #111827; }

        /* ── Password toggle ── */
        .input-group .btn-outline-secondary {
            border-color: #ccd8ea;
            color: #5a7a9a;
            background: #ffffff;
            transition: background 0.2s, color 0.2s;
        }
        .input-group .btn-outline-secondary:hover {
            background: #dde8f5;
            color: var(--navy);
            border-color: #b0c8e0;
        }

        /* ── Checkbox ── */
        .form-check-label { color: #4b6a8a; font-size: 13px; }
        .form-check-input {
            background-color: #ffffff;
            border-color: #b8cce0;
        }
        .form-check-input:checked {
            background-color: var(--teal);
            border-color: var(--teal);
        }

        /* ── Primary button — brand teal ── */
        .btn-primary {
            background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(29,184,126,0.28);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(29,184,126,0.4);
            background: linear-gradient(135deg, var(--teal-light) 0%, var(--teal) 100%);
            color: #fff;
        }
        .btn-primary:active { transform: translateY(0); }

        /* ── Alerts — readable on white card ── */
        .alert {
            border-radius: 10px;
            border: 1px solid transparent;
            font-size: 13.5px;
            margin-bottom: 16px;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        .alert-warning {
            background: #fffbeb;
            color: #92400e;
            border-color: #fde68a;
        }
        .alert-danger {
            background: #fff1f2;
            color: #9f1239;
            border-color: #fecdd3;
        }

        /* ── Links ── */
        a { color: var(--teal-dark); transition: color 0.2s; }
        a:hover { color: var(--navy); }

        /* ── Validation ── */
        .invalid-feedback { color: #dc2626; font-size: 12px; }
        .is-invalid { border-color: rgba(239,68,68,0.55) !important; }

        /* ── Divider ── */
        .auth-divider {
            height: 1px;
            background: #cdd8ea;
            margin: 20px 0;
        }

        /* ── Footer — slightly deeper blue-gray ── */
        .auth-footer {
            text-align: center;
            padding: 12px;
            font-size: 11px;
            color: #6b84a0;
            border-top: 1px solid #cdd8ea;
            background: #dde8f5;
        }

        /* ── Responsive ── */
        @media (max-width: 576px) {
            .auth-shell  { padding: 12px; }
            .auth-header { padding: 24px 20px 16px; }
            .auth-body   { padding: 0 20px 24px; }
            .auth-logo-wrap { width: 80px; height: 80px; border-radius: 16px; }
            .auth-logo-wrap img { width: 60px; height: 60px; }
        }
    </style>
</head>
<body>
    {{-- Decorative orbs --}}
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>

    {{-- Static CSS star dots --}}
    <div class="stars" aria-hidden="true">
        <div class="star" style="width:2px;height:2px;top:8%;left:15%;animation-duration:4.1s;"></div>
        <div class="star" style="width:1px;height:1px;top:22%;left:72%;animation-duration:6.3s;"></div>
        <div class="star" style="width:2px;height:2px;top:55%;left:8%;animation-duration:5.2s;"></div>
        <div class="star" style="width:1px;height:1px;top:38%;left:88%;animation-duration:3.8s;"></div>
        <div class="star" style="width:2px;height:2px;top:78%;left:44%;animation-duration:7.1s;"></div>
        <div class="star" style="width:1px;height:1px;top:15%;left:56%;animation-duration:4.9s;"></div>
        <div class="star" style="width:2px;height:2px;top:65%;left:91%;animation-duration:6.0s;"></div>
        <div class="star" style="width:1px;height:1px;top:48%;left:30%;animation-duration:5.5s;"></div>
        <div class="star" style="width:2px;height:2px;top:90%;left:68%;animation-duration:3.6s;"></div>
        <div class="star" style="width:1px;height:1px;top:34%;left:4%;animation-duration:8.2s;"></div>
        <div class="star" style="width:2px;height:2px;top:5%;left:82%;animation-duration:4.4s;"></div>
        <div class="star" style="width:1px;height:1px;top:73%;left:20%;animation-duration:6.8s;"></div>
        <div class="star" style="width:2px;height:2px;top:28%;left:64%;animation-duration:5.7s;"></div>
        <div class="star" style="width:1px;height:1px;top:84%;left:37%;animation-duration:4.0s;"></div>
        <div class="star" style="width:2px;height:2px;top:18%;left:48%;animation-duration:7.4s;"></div>
        <div class="star" style="width:1px;height:1px;top:60%;left:77%;animation-duration:3.3s;"></div>
        <div class="star" style="width:2px;height:2px;top:42%;left:52%;animation-duration:5.9s;"></div>
        <div class="star" style="width:1px;height:1px;top:95%;left:10%;animation-duration:6.5s;"></div>
        <div class="star" style="width:2px;height:2px;top:3%;left:35%;animation-duration:4.7s;"></div>
        <div class="star" style="width:1px;height:1px;top:70%;left:60%;animation-duration:8.0s;"></div>
    </div>

    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-card-top"></div>

            <div class="auth-header">
                <div class="auth-logo-wrap">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="SafeTrack Logo"
                         onerror="this.parentElement.innerHTML='<i class=\'fas fa-shield-alt\' style=\'font-size:36px;color:#1db87e;\'></i>'">
                </div>
                <h1><span class="brand-safe">Safe</span><span class="brand-track">Track</span></h1>
                <p>@yield('subtitle', 'Barangay safety &amp; household management')</p>
            </div>

            <div class="auth-body">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                    </div>
                @endif

                @if(!$errors->any() && session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>

            <div class="auth-footer">
                &copy; {{ date('Y') }} SafeTrack &mdash; Barangay Management System
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

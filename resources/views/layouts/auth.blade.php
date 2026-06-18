<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SafeTrack')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Google Fonts loaded non-blocking --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            /* Refined dark teal-blue gradient for visual cohesion */
            background: linear-gradient(160deg, #1e3a50 0%, #122737 40%, #173245 70%, #101b27 100%);
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
        }

        /* ── Static decorative circles (CSS only, no JS) ── */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* One large charcoal/gray orb top-left — CSS animation, GPU-composited (opacity+transform only) */
        .bg-orb-1 {
            width: 500px; height: 500px;
            top: -180px; left: -150px;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
            /* Use transform+opacity only — no filter — so it stays on the compositor thread */
            animation: orbDrift1 18s ease-in-out infinite;
            will-change: transform;
        }

        .bg-orb-2 {
            width: 380px; height: 380px;
            bottom: -100px; right: -100px;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
            animation: orbDrift2 22s ease-in-out infinite;
            will-change: transform;
        }

        @keyframes orbDrift1 {
            0%, 100% { transform: translate(0, 0); }
            50%       { transform: translate(24px, -20px); }
        }
        @keyframes orbDrift2 {
            0%, 100% { transform: translate(0, 0); }
            50%       { transform: translate(-20px, 18px); }
        }

        /* ── Light star dots — CSS only, no canvas ── */
        .stars {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .star {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.45);
            /* twinkle: only opacity — GPU composited */
            animation: twinkle linear infinite;
            will-change: opacity;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.15; }
            50%       { opacity: 0.8; }
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

        /* ── Card — semi-transparent, lightweight ── */
        .auth-card {
            width: min(100%, 460px);
            background: rgba(18, 18, 20, 0.85);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow:
                0 4px 24px rgba(0, 0, 0, 0.65),
                0 1px 0 rgba(255,255,255,0.06) inset;
            overflow: hidden;
            /* Entrance: opacity+transform only */
            animation: cardIn 0.5s ease-out both;
            will-change: transform, opacity;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Top shimmer bar — CSS gradient animation only */
        .auth-card-top {
            height: 3px;
            background: linear-gradient(90deg,
                #27272a 0%, #52525b 33%, #a1a1aa 66%, #27272a 100%);
            background-size: 300% 100%;
            animation: shimmer 5s linear infinite;
        }

        @keyframes shimmer {
            0%   { background-position: 0% 0%; }
            100% { background-position: 300% 0%; }
        }

        /* ── Header ── */
        .auth-header {
            padding: 30px 30px 18px;
            text-align: center;
        }

        .auth-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 68px; height: 68px;
            border-radius: 16px;
            background: linear-gradient(135deg,
                rgba(255,255,255,0.08), rgba(255,255,255,0.03));
            border: 1px solid rgba(255,255,255,0.12);
            margin-bottom: 14px;
        }

        .auth-logo-wrap img {
            width: 44px; height: 44px;
            object-fit: contain;
            border-radius: 8px;
        }

        .auth-header h1 {
            margin: 0 0 6px;
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .auth-header h1 span {
            background: linear-gradient(90deg, #ffffff, #d4d4d8, #a1a1aa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-header p {
            margin: 0;
            color: rgba(255,255,255,0.45);
            font-size: 13px;
        }

        /* ── Body ── */
        .auth-body {
            padding: 4px 30px 28px;
        }

        /* ── Labels ── */
        .form-label {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        /* ── Inputs ── */
        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 9px;
            color: #e2e8f0;
            padding: 11px 14px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder { color: rgba(255,255,255,0.25); }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255,255,255,0.09);
            border-color: rgba(255,255,255,0.25);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.06);
            color: #fff;
            outline: none;
        }

        .form-select option { background: #1e2d52; color: #fff; }

        /* ── Password toggle ── */
        .input-group .btn-outline-secondary {
            border-color: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.45);
            background: rgba(255,255,255,0.04);
            transition: background 0.2s, color 0.2s;
        }
        .input-group .btn-outline-secondary:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-color: rgba(255,255,255,0.15);
        }

        /* ── Checkbox ── */
        .form-check-label { color: rgba(255,255,255,0.55); font-size: 13px; }
        .form-check-input {
            background-color: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.18);
        }
        .form-check-input:checked {
            background-color: #000000;
            border-color: #3f3f46;
        }

        /* ── Primary button ── */
        .btn-primary {
            background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 9px;
            padding: 11px 20px;
            font-weight: 600;
            font-size: 14px;
            color: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.5);
            background: linear-gradient(135deg, #27272a 0%, #18181b 100%);
            color: #fff;
        }
        .btn-primary:active { transform: translateY(0); }

        /* ── Alerts ── */
        .alert {
            border-radius: 9px;
            border: 1px solid transparent;
            font-size: 13.5px;
            margin-bottom: 16px;
        }
        .alert-success {
            background: rgba(16,185,129,0.12);
            color: #6ee7b7;
            border-color: rgba(16,185,129,0.22);
        }
        .alert-warning {
            background: rgba(245,158,11,0.12);
            color: #fcd34d;
            border-color: rgba(245,158,11,0.22);
        }
        .alert-danger {
            background: rgba(239,68,68,0.12);
            color: #fca5a5;
            border-color: rgba(239,68,68,0.22);
        }

        /* ── Links ── */
        a { color: #d4d4d8; transition: color 0.2s; }
        a:hover { color: #ffffff; }

        /* ── Validation ── */
        .invalid-feedback { color: #fca5a5; font-size: 12px; }

        /* ── Footer ── */
        .auth-footer {
            text-align: center;
            padding: 12px;
            font-size: 11px;
            color: rgba(255,255,255,0.2);
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        /* ── Responsive ── */
        @media (max-width: 576px) {
            .auth-shell  { padding: 12px; }
            .auth-header { padding: 22px 18px 14px; }
            .auth-body   { padding: 0 18px 22px; }
        }
    </style>
</head>
<body>
    {{-- Lightweight CSS-only decorative orbs (no blur filter, no canvas) --}}
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>

    {{-- Static CSS star dots (generated inline, no JS loop) --}}
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
                         onerror="this.parentElement.innerHTML='<i class=\'fas fa-shield-alt\' style=\'font-size:26px;color:#e4e4e7;\'></i>'">
                </div>
                <h1><span>SafeTrack</span></h1>
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

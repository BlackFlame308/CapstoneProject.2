<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeTrack – Barangay Household Profiling System</title>
    <meta name="description" content="SafeTrack is a barangay-level profiling and data intelligence system for centralized household management and analytics.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:      #1a3a5c;
            --navy-dark: #0d2338;
            --navy-mid:  #1e4a72;
            --teal:      #1db87e;
            --teal-dark: #159962;
            --teal-light:#22d492;
            --white:     #ffffff;
            --gray-100:  #f0f4f8;
            --gray-200:  #dde3ec;
            --gray-500:  #6b7a99;
            --gray-700:  #3d4f66;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(160deg, var(--navy-dark) 0%, #0f2d4a 35%, #153850 65%, var(--navy-dark) 100%);
            min-height: 100vh;
            color: var(--white);
            overflow-x: hidden;
        }

        /* ── Animated background mesh ── */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .bg-mesh::before {
            content: '';
            position: absolute;
            top: -30%; left: -20%;
            width: 80%; height: 80%;
            background: radial-gradient(ellipse, rgba(29,184,126,0.12) 0%, transparent 65%);
            animation: meshDrift1 20s ease-in-out infinite;
        }
        .bg-mesh::after {
            content: '';
            position: absolute;
            bottom: -20%; right: -15%;
            width: 70%; height: 70%;
            background: radial-gradient(ellipse, rgba(30,74,114,0.25) 0%, transparent 65%);
            animation: meshDrift2 25s ease-in-out infinite;
        }
        @keyframes meshDrift1 {
            0%,100% { transform: translate(0,0) scale(1); }
            50%      { transform: translate(40px,-30px) scale(1.08); }
        }
        @keyframes meshDrift2 {
            0%,100% { transform: translate(0,0) scale(1); }
            50%      { transform: translate(-30px,25px) scale(1.05); }
        }

        /* ── Star dots ── */
        .stars { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .star {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            animation: twinkle linear infinite;
            will-change: opacity;
        }
        @keyframes twinkle {
            0%,100% { opacity: 0.1; }
            50%      { opacity: 0.7; }
        }

        /* ── Layout ── */
        .page-wrap {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* ── Top nav bar ── */
        .topbar {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 48px;
            background: rgba(13,35,56,0.5);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .topbar-logo {
            width: 40px; height: 40px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(29,184,126,0.3));
        }
        .topbar-name {
            font-size: 20px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.3px;
        }
        .topbar-name span { color: var(--teal-light); }

        /* ── Hero ── */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 80px 24px 60px;
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
        }

        /* Logo showcase */
        .logo-showcase {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 36px;
        }
        .logo-glow {
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(29,184,126,0.25) 0%, transparent 70%);
            filter: blur(20px);
            animation: glowPulse 3s ease-in-out infinite;
        }
        @keyframes glowPulse {
            0%,100% { opacity: 0.6; transform: scale(1); }
            50%      { opacity: 1;   transform: scale(1.1); }
        }
        .logo-ring {
            position: relative;
            width: 140px; height: 140px;
            border-radius: 28px;
            background: rgba(255,255,255,0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.3),
                0 8px 40px rgba(0,0,0,0.3),
                0 0 60px rgba(29,184,126,0.2);
            animation: logoFloat 4s ease-in-out infinite;
        }
        @keyframes logoFloat {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
        .logo-img {
            width: 110px; height: 110px;
            object-fit: contain;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(29,184,126,0.15);
            border: 1px solid rgba(29,184,126,0.3);
            border-radius: 40px;
            padding: 5px 16px;
            font-size: 12px;
            font-weight: 600;
            color: var(--teal-light);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .hero-badge i { font-size: 10px; }

        .hero h1 {
            font-size: clamp(36px, 5vw, 60px);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -1.5px;
            margin-bottom: 22px;
            color: var(--white);
        }
        .hero h1 .brand-safe { color: var(--white); }
        .hero h1 .brand-track {
            background: linear-gradient(90deg, var(--teal), var(--teal-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 18px;
            line-height: 1.65;
            color: rgba(255,255,255,0.65);
            max-width: 580px;
            margin: 0 auto 40px;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 15px 32px;
            background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 24px rgba(29,184,126,0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(29,184,126,0.5);
            color: white;
        }
        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 15px 32px;
            background: rgba(255,255,255,0.07);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.15);
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, transform 0.2s;
        }
        .btn-hero-secondary:hover {
            background: rgba(255,255,255,0.13);
            border-color: rgba(255,255,255,0.25);
            color: white;
            transform: translateY(-2px);
        }

        /* ── Feature cards ── */
        .features {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .feature-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(8px);
            transition: transform 0.25s, border-color 0.25s, background 0.25s;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(29,184,126,0.35);
            background: rgba(29,184,126,0.06);
        }
        .feature-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
            background: linear-gradient(135deg, rgba(29,184,126,0.2), rgba(29,184,126,0.05));
            color: var(--teal-light);
            border: 1px solid rgba(29,184,126,0.2);
        }
        .feature-card h4 {
            font-size: 14px;
            font-weight: 700;
            color: white;
            margin-bottom: 6px;
        }
        .feature-card p {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            line-height: 1.5;
        }

        /* ── Login card ── */
        .login-section {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }
        .login-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.11);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        .login-card-info {
            flex: 1;
            min-width: 200px;
        }
        .login-card-info h3 {
            font-size: 22px;
            font-weight: 800;
            color: white;
            margin-bottom: 10px;
        }
        .login-card-info p {
            font-size: 14px;
            color: rgba(255,255,255,0.55);
            line-height: 1.6;
        }
        .login-card-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 200px;
        }

        /* ── Demo credentials ── */
        .demo-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            font-size: 12px;
            color: rgba(255,255,255,0.55);
        }
        .demo-pill code {
            color: var(--teal-light);
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        /* ── Footer ── */
        .footer {
            width: 100%;
            text-align: center;
            padding: 20px 24px;
            font-size: 12px;
            color: rgba(255,255,255,0.2);
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        @media (max-width: 640px) {
            .topbar { padding: 16px 20px; }
            .hero { padding: 50px 20px 40px; }
            .logo-ring { width: 110px; height: 110px; }
            .logo-img { width: 85px; height: 85px; }
            .login-card { padding: 28px 20px; gap: 24px; }
            .features { padding: 0 20px 60px; }
        }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>

    <!-- Star dots -->
    <div class="stars" aria-hidden="true">
        <div class="star" style="width:2px;height:2px;top:7%;left:14%;animation-duration:4.2s;"></div>
        <div class="star" style="width:1px;height:1px;top:21%;left:73%;animation-duration:6.1s;"></div>
        <div class="star" style="width:2px;height:2px;top:54%;left:9%;animation-duration:5.3s;"></div>
        <div class="star" style="width:1px;height:1px;top:37%;left:87%;animation-duration:3.9s;"></div>
        <div class="star" style="width:2px;height:2px;top:77%;left:45%;animation-duration:7.0s;"></div>
        <div class="star" style="width:1px;height:1px;top:16%;left:57%;animation-duration:4.8s;"></div>
        <div class="star" style="width:2px;height:2px;top:64%;left:92%;animation-duration:6.2s;"></div>
        <div class="star" style="width:1px;height:1px;top:48%;left:31%;animation-duration:5.6s;"></div>
        <div class="star" style="width:2px;height:2px;top:89%;left:67%;animation-duration:3.7s;"></div>
        <div class="star" style="width:1px;height:1px;top:33%;left:5%;animation-duration:8.1s;"></div>
        <div class="star" style="width:2px;height:2px;top:6%;left:83%;animation-duration:4.5s;"></div>
        <div class="star" style="width:1px;height:1px;top:74%;left:21%;animation-duration:6.9s;"></div>
        <div class="star" style="width:2px;height:2px;top:27%;left:63%;animation-duration:5.8s;"></div>
        <div class="star" style="width:1px;height:1px;top:83%;left:38%;animation-duration:4.1s;"></div>
        <div class="star" style="width:2px;height:2px;top:18%;left:47%;animation-duration:7.5s;"></div>
    </div>

    <div class="page-wrap">
        <!-- Top navigation bar -->
        <header class="topbar">
            <a href="#" class="topbar-brand">
                <img src="{{ asset('images/logo.png') }}" alt="SafeTrack Logo" class="topbar-logo"
                     onerror="this.style.display='none'">
                <span class="topbar-name"><span class="brand-safe">Safe</span><span style="color:#22d492;">Track</span></span>
            </a>
            <div style="font-size:12px; color:rgba(255,255,255,0.35); font-weight:500;">
                Barangay Management System
            </div>
        </header>

        <!-- Hero section -->
        <section class="hero">
            <!-- Prominent logo -->
            <div class="logo-showcase">
                <div class="logo-glow"></div>
                <div class="logo-ring">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="SafeTrack Logo"
                         class="logo-img"
                         onerror="this.parentElement.innerHTML='<i class=\'fas fa-shield-alt\' style=\'font-size:52px;color:#1db87e;\'></i>'">
                </div>
            </div>

            <div class="hero-badge">
                <i class="fas fa-shield-alt"></i>
                Barangay Safety & Profiling
            </div>

            <h1>
                <span class="brand-safe">Safe</span><span class="brand-track">Track</span><br>
                <span style="font-size: 0.55em; font-weight:600; color:rgba(255,255,255,0.6); letter-spacing:-0.5px;">
                    Central Household Profiling &amp; Analytics System
                </span>
            </h1>

            <p>
                A barangay-level data intelligence platform that centralizes household profiling,
                resident management, and population analytics — empowering data-driven decisions for your community.
            </p>

            <div class="hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" id="goto-dashboard-btn" class="btn-hero-primary">
                        <i class="fas fa-chart-line"></i> Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" id="login-btn" class="btn-hero-primary">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </a>
                    @if (App\Models\User::count() === 0)
                        <a href="{{ route('register') }}" id="register-btn" class="btn-hero-secondary">
                            <i class="fas fa-user-plus"></i> Register Admin
                        </a>
                    @endif
                @endauth
            </div>
        </section>

        <!-- Feature cards -->
        <section class="features">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-home"></i></div>
                    <h4>Household Management</h4>
                    <p>Register and manage all barangay households with complete profiling data.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h4>Resident Profiling</h4>
                    <p>Track residents including vulnerable groups — seniors, children, and PWDs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                    <h4>Analytics & Reports</h4>
                    <p>Generate meaningful population analytics and subsystem reports on demand.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-csv"></i></div>
                    <h4>CSV Import</h4>
                    <p>Bulk import household data via CSV for fast, efficient data entry.</p>
                </div>
            </div>
        </section>

        <!-- Login/access section -->
        <section class="login-section">
            <div class="login-card">
                <div class="login-card-info">
                    <h3>Ready to get started?</h3>
                    <p>Sign in to your account to access the dashboard, manage households, and view community analytics.</p>
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:8px;">
                        <div class="demo-pill">
                            <i class="fas fa-envelope" style="color:var(--teal-light);font-size:11px;"></i>
                            Email: <code>captain@safetrack.local</code>
                        </div>
                        <div class="demo-pill">
                            <i class="fas fa-key" style="color:var(--teal-light);font-size:11px;"></i>
                            Pass: <code>password</code>
                        </div>
                    </div>
                </div>
                <div class="login-card-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-hero-primary" style="justify-content:center;">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-hero-primary" style="justify-content:center;">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                        @if (App\Models\User::count() === 0)
                            <a href="{{ route('register') }}" class="btn-hero-secondary" style="justify-content:center;">
                                <i class="fas fa-user-plus"></i> Register Admin
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            &copy; {{ date('Y') }} SafeTrack &mdash; Barangay Household Profiling &amp; Analytics System
        </footer>
    </div>
</body>
</html>

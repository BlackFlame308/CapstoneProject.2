<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SafeTrack')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: #0f172a;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .auth-card {
            width: min(100%, 460px);
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #3B82F6;
            overflow: hidden;
        }

        .auth-header {
            padding: 28px 28px 18px;
            text-align: center;
        }

        .auth-header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
        }

        .auth-header p {
            margin: 8px 0 0;
            color: #6B7280;
            font-size: 14px;
        }

        .auth-body {
            padding: 0 28px 28px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 0;
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 10px 14px;
        }

        @media (max-width: 576px) {
            .auth-shell {
                padding: 12px;
            }

            .auth-header,
            .auth-body {
                padding-left: 18px;
                padding-right: 18px;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-header">
                <h1>
                    <img src="{{ asset('images/logo.png') }}"
                         alt="SafeTrack Logo"
                         style="width: 48px; height: 48px; object-fit: contain; vertical-align: middle; margin-right: 8px; border-radius: 8px; background: #fff; padding: 2px;"
                         onerror="this.style.display='none'; document.getElementById('auth-fallback-icon').style.display='inline';">
                    <i id="auth-fallback-icon" class="fas fa-shield-alt text-primary" style="display:none;"></i>
                    SafeTrack
                </h1>
                <p>@yield('subtitle', 'Barangay safety and household management')</p>
            </div>
            <div class="auth-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                @yield('content')
            </div>
        </section>
    </main>
</body>
</html>

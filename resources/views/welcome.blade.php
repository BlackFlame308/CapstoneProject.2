<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeTrack - Household Profiling System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border-radius: 10px;
        }
        .welcome-container {
            max-width: 600px;
        }
        .hero-text {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        .hero-text h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .hero-text p {
            font-size: 18px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="hero-text">
            <h1>SafeTrack</h1>
            <p>Central Household Profiling & Analytics System</p>
        </div>

        <div class="card">
            <div class="card-body p-5">
                <h3 class="card-title text-center mb-4">Welcome to SafeTrack</h3>
                
                <p class="text-muted mb-4">
                    SafeTrack is a barangay-level profiling and data intelligence system designed to:
                </p>

                <ul class="mb-4">
                    <li>Centralize household and resident profiling</li>
                    <li>Provide accurate population data</li>
                    <li>Support data-driven barangay decisions</li>
                    <li>Generate analytics and reports</li>
                </ul>

                <div class="d-grid gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
                        @if (App\Models\User::count() === 0)
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Register Admin</a>
                        @endif
                    @endauth
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6 text-center mb-3">
                        <h6>System Features</h6>
                        <p class="small text-muted">Household management, profiling, analytics</p>
                    </div>
                    <div class="col-md-6 text-center mb-3">
                        <h6>Demo Account</h6>
                        <p class="small text-muted">
                            Email: captain@safetrack.local<br>
                            Pass: password
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

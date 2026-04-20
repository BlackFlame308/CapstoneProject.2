<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SafeTrack')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sidebar {
            background: white;
            min-height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }
        .main-content {
            padding: 30px;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: none;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            @auth
            <a class="navbar-brand" href="{{ route('dashboard') }}">SafeTrack</a>
            @else
            <a class="navbar-brand" href="{{ route('login') }}">SafeTrack</a>
            @endauth
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    @if (App\Models\User::count() === 0)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                    @endif
                    @endguest

                    @auth
                    <li class="nav-item">
                        <span class="nav-link">{{ auth()->user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="nav-link btn btn-link" type="submit">Logout</button>
                        </form>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="row g-0">
        @auth
        <div class="col-md-3">
            <div class="sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    @can('view_households')
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('households.index') }}">Households</a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('households.create') }}">Add Household</a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('csv.upload') }}">Upload CSV</a>
                    </li>
                    @endcan
                    @can('manage_accounts')
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('accounts.index') }}">Accounts</a>
                    </li>
                    @endcan
                    @can('register_accounts')
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('register') }}">Register Account</a>
                    </li>
                    @endcan
                    @can('view_reports')
                    <li class="nav-item mb-3">
                        <a class="nav-link" href="{{ route('dashboard') }}">System Reports</a>
                    </li>
                    @endcan
                </ul>
            </div>
        </div>
        <div class="col-md-9">
            <div class="main-content">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </div>
        @else
        <div class="col-12">
            <div class="main-content">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </div>
        @endauth
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>

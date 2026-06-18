<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SafeTrack Household Portal')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #edf0f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #09090b 0%, #18181b 50%, #27272a 100%);
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 30px 20px;
            color: white;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(0,0,0,0.25);
        }

        .sidebar-header h3 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-header small {
            display: block;
            opacity: 0.8;
            font-size: 12px;
            margin-top: 5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu a {
            color: #ffffff;
            padding: 15px 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #ffffff;
            font-weight: 600;
        }

        .sidebar-logout {
            width: 100%;
            padding: 15px 25px;
            background: none;
            border: none;
            color: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sidebar-logout:hover {
            background-color: rgba(255,255,255,0.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation */
        .navbar-top {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .user-info small {
            color: #999;
            display: block;
            font-size: 12px;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
            flex: 1;
        }

        /* Card Styles */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            border: none;
            border-top: 4px solid #18181b;
            height: 100%;
        }

        .stat-card:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .stat-card .stat-icon {
            font-size: 28px;
            margin-bottom: 10px;
            color: #18181b;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .stat-card .stat-label {
            color: #888;
            font-size: 13px;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
                transition: all 0.3s ease;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar.active {
                width: 280px;
                transform: translateX(0);
            }

            .content-area {
                padding: 15px;
            }

            .navbar-top {
                padding: 15px 20px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>
                <i class="fas fa-shield-alt"></i> SafeTrack
            </h3>
            <small>Household Portal</small>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('household.dashboard') }}" class="@if(Request::routeIs('household.dashboard')) active @endif">
                    <i class="fas fa-chart-line"></i>
                    <span>My Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('password.change') }}" class="@if(Request::routeIs('password.change')) active @endif">
                    <i class="fas fa-lock"></i>
                    <span>Change Password</span>
                </a>
            </li>

            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">

            <li>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="sidebar-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar-top">
            <div>
                <h4 style="margin: 0; color: #333; font-weight: 600;">@yield('page_title', 'Household Dashboard')</h4>
            </div>

            <div class="user-profile">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #18181b 0%, #09090b 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <h6>{{ auth()->user()->name }}</h6>
                    <small>Household Representative</small>
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alerts -->
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border: none; border-radius: 8px;">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border: none; border-radius: 8px;">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border: none; border-radius: 8px;">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

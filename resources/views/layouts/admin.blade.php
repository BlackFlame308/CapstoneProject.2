<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SafeTrack Admin')</title>
    
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
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e3a5f 50%, #1e40af 100%);
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 30px 20px;
            color: white;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0,0,0,0.3);
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

        .sidebar-menu li {
            margin: 0;
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
            background-color: rgba(59, 130, 246, 0.3);
            border-left: 4px solid #3B82F6;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
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

        .navbar-top-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-top-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
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

        .page-title {
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title h4 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
            flex: 1;
            overflow-y: auto;
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Card Styles */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: none;
            transition: all 0.3s ease;
            border-top: 4px solid #667eea;
        }

        .stat-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .stat-card .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: #667eea;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #999;
            font-size: 14px;
        }

        /* Table Styles */
        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .table thead th {
            font-weight: 600;
            color: #333;
            padding: 15px;
            border: none;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f1f1;
        }

        .table tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Form Styles */
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Button Styles */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #333;
        }

        .btn-info {
            background-color: #17a2b8;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
        }

        /* Badge Styles */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Breadcrumb */
        .breadcrumb {
            background-color: transparent;
            margin-bottom: 20px;
            padding: 0;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #999;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #bbb;
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
            }

            .sidebar.active {
                width: 280px;
                transform: translateX(0);
            }

            .content-area {
                padding: 20px;
            }

            .navbar-top {
                padding: 15px 20px;
            }
        }

        /* Loading Spinner */
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            <small>Admin Dashboard</small>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="/dashboard"
                   class="@if(Request::is('dashboard')) active @endif">
                    <span>🏠</span>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="/admin/households"
                   class="@if(Request::is('admin/households*')) active @endif">
                    <span>🏘️</span>
                    <span>Household Management</span>
                </a>
            </li>

            <li>
                <a href="/csv/upload"
                   class="@if(Request::is('csv/upload*')) active @endif">
                    <span>📂</span>
                    <span>Upload CSV</span>
                </a>
            </li>

            <li>
                <a href="/admin/residents"
                   class="@if(Request::is('admin/residents*')) active @endif">
                    <span>👥</span>
                    <span>Resident/Member Management</span>
                </a>
            </li>

            <li>
                <a href="/admin/accounts"
                   class="@if(Request::is('admin/accounts*')) active @endif">
                    <span>👤</span>
                    <span>Account Management</span>
                </a>
            </li>

            @if(strtolower(auth()->user()->role?->name ?? '') === 'captain' 
|| strtolower(auth()->user()->role?->name ?? '') === 'head')
            <li>
                <a href="/admin/analytics"
                   class="@if(Request::is('admin/analytics*')) active @endif">
                    <span>📊</span>
                    <span>Analytics View</span>
                </a>
            </li>

            <li>
                <a href="/admin/reports"
                   class="@if(Request::is('admin/reports*')) active @endif">
                    <span>📋</span>
                    <span>Reports View</span>
                </a>
            </li>

            <li>
                <a href="/admin/tokens"
                   class="@if(Request::is('admin/tokens*')) active @endif">
                    <span>🔑</span>
                    <span>API Token Management</span>
                </a>
            </li>
            @endif

            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">

            <li>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: none; border: none; width: 100%; text-align: left;">
                        <a style="padding: 15px 25px; display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.9); cursor: pointer;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar-top">
            <div class="navbar-top-left">
                <div class="page-title">
                    @yield('page_icon')
                    <h4>@yield('page_title', 'Dashboard')</h4>
                </div>
            </div>

            <div class="navbar-top-right">
                <div class="user-profile">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #667eea; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <h6>{{ auth()->user()->name }}</h6>
                        <small>
                            @if(auth()->user()->role?->name)
                                {{ ucfirst(auth()->user()->role->name) }}
                            @else
                                User
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alerts -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>
</html>

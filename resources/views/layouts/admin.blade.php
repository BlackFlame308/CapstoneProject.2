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
    <!-- Admin CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
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
            overflow-x: hidden;
        }

        img,
        svg,
        canvas,
        video {
            max-width: 100%;
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
            min-width: 0;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255,255,255,0.1);
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

        .sidebar-menu a.active {
            background-color: rgba(59, 130, 246, 0.3);
            border-left: 4px solid #3B82F6;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        .sidebar-menu span:last-child {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-width: 0;
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
            min-width: 0;
        }

        .navbar-top-right {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
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
            min-width: 0;
        }

        .page-title h4 {
            margin: 0;
            color: #333;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
            flex: 1;
            overflow-y: auto;
            min-width: 0;
            max-width: 100%;
        }

        .content-area > .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }

        .row,
        [class^="col-"],
        [class*=" col-"] {
            min-width: 0;
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
            height: 100%;
            min-width: 0;
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
            overflow-wrap: anywhere;
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
            min-width: 0;
        }

        .table-responsive {
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table th,
        .table td {
            overflow-wrap: anywhere;
        }

        .card,
        .card-header,
        .card-body,
        .card-footer,
        .list-group-item {
            min-width: 0;
        }

        .card-header > div,
        .list-group-item {
            gap: 12px;
        }

        .card-header[style*="display: flex"],
        .content-area [style*="display: flex"] {
            flex-wrap: wrap;
            gap: 12px;
        }

        .card-title {
            margin-bottom: 0;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .card-tools,
        .btn-toolbar,
        .btn-group-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn {
            white-space: normal;
        }

        .badge {
            white-space: normal;
            overflow-wrap: anywhere;
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
                width: 100%;
            }

            .sidebar.active {
                width: 280px;
                transform: translateX(0);
            }

            .content-area {
                padding: 16px;
            }

            .navbar-top {
                padding: 15px 20px;
                align-items: flex-start;
                gap: 12px;
                flex-wrap: wrap;
            }

            .navbar-top-right {
                width: 100%;
                justify-content: flex-start;
            }

            .card-header > div,
            .list-group-item {
                align-items: flex-start !important;
                flex-wrap: wrap;
            }

            .card-header {
                padding: 16px !important;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-card .stat-value {
                font-size: 26px;
            }

            .table {
                font-size: 13px;
            }
        }

        @media (max-width: 576px) {
            .content-area {
                padding: 12px;
            }

            .user-profile {
                align-items: flex-start;
            }

            .user-info h6,
            .user-info small {
                overflow-wrap: anywhere;
            }

            .btn,
            .form-control,
            .form-select {
                width: 100%;
            }

            .table .btn,
            .card-header .btn,
            .list-group-item .btn {
                width: auto;
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
        @php
            $user = auth()->user();
            $canManageAccounts = $user?->canManageAccounts() ?? false;
            $canViewReports = ($user?->hasPermission('view_reports') ?? false) || ($user?->isSuperAdmin() ?? false);
        @endphp

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="@if(Request::routeIs('admin.dashboard')) active @endif">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.households.index') }}" class="@if(Request::routeIs('admin.households.*')) active @endif">
                    <i class="fas fa-home"></i>
                    <span>Household Management</span>
                </a>
            </li>

            <li>
                <a href="{{ route('csv.upload') }}" class="@if(Request::routeIs('csv.upload')) active @endif">
                    <i class="fas fa-file-csv"></i>
                    <span>Upload Demographics</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.residents.index') }}" class="@if(Request::routeIs('admin.residents.*')) active @endif">
                    <i class="fas fa-users"></i>
                    <span>Resident/Member Management</span>
                </a>
            </li>



            @if($canManageAccounts)
                <li>
                    <a href="{{ route('admin.accounts.index') }}" class="@if(Request::routeIs('admin.accounts.*')) active @endif">
                        <i class="fas fa-user-gear"></i>
                        <span>Account Management</span>
                    </a>
                </li>
            @endif

            @if($canViewReports)
                <li>
                    <a href="{{ route('admin.analytics.index') }}" class="@if(Request::routeIs('admin.analytics.*')) active @endif">
                        <i class="fas fa-chart-pie"></i>
                        <span>Analytics View</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.reports.index') }}" class="@if(Request::routeIs('admin.reports.*')) active @endif">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Reports View</span>
                    </a>
                </li>
            @endif

            @if($canManageAccounts)
                <li>
                    <a href="{{ route('admin.tokens.index') }}" class="@if(Request::routeIs('admin.tokens.*')) active @endif">
                        <i class="fas fa-key"></i>
                        <span>API Token Management</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.csv-import.index') }}" class="@if(Request::routeIs('admin.csv-import.*')) active @endif">
                        <i class="fas fa-upload"></i>
                        <span>CSV Import Dashboard</span>
                    </a>
                </li>
            @endif

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

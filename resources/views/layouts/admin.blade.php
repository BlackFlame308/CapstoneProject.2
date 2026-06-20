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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --sidebar-w: 272px;
            --sidebar-bg-start: #1f3042;
            --sidebar-bg-end:   #101921;
            --accent-blue:  #102b43;
            --accent-indigo:#1a4060;
            --accent-purple:#5f6f87;
            --content-bg:   #eff4fa;
            --navbar-bg:    rgba(255,255,255,0.96);
            --card-bg:      #ffffff;
            --text-main:    #111827;
            --text-muted:   #596077;
            --border:       rgba(0,0,0,0.08);
            --shadow-sm:    0 2px 8px rgba(0,0,0,0.05);
            --shadow-md:    0 6px 24 rgba(0,0,0,0.08);
            --shadow-lg:    0 12px 40px rgba(0,0,0,0.12);
            --radius-sm:    8px;
            --radius-md:    12px;
            --radius-lg:    16px;
            --transition:   0.25s cubic-bezier(.4,0,.2,1);
        }

        html, body {
            height: 100%;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--content-bg);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            overflow-x: hidden;
            color: var(--text-main);
        }

        img, svg, canvas, video {
            max-width: 100%;
        }

        /* ════════════════════════════════════════
           SIDEBAR
        ════════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg,
                var(--sidebar-bg-start) 0%,
                #152639 40%,
                var(--sidebar-bg-end) 100%);
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            /* Subtle dark glow along the right edge */
            box-shadow: 4px 0 30px rgba(0,0,0,0.25);
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
            /* Subtle entrance animation */
            animation: sidebarLoad 0.6s ease-out;
        }

        @keyframes sidebarLoad {
            from { opacity: 0.95; transform: translateX(-8px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.12);
            border-radius: 4px;
        }

        /* ── Animated top bar inside sidebar ── */
        .sidebar::before {
            content: '';
            display: block;
            height: 3px;
            flex-shrink: 0;
            background: linear-gradient(90deg,
                var(--accent-blue),
                var(--accent-indigo),
                var(--accent-purple),
                var(--accent-blue));
            background-size: 300% 100%;
            animation: shimmer 4s linear infinite;
        }

        @keyframes shimmer {
            0%   { background-position: 0%   0%; }
            100% { background-position: 300% 0%; }
        }

        /* ── Sidebar header ── */
        .sidebar-header {
            padding: 28px 20px 22px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            position: relative;
        }

        /* Subtle glow halo behind logo */
        .sidebar-header::after {
            content: '';
            position: absolute;
            top: 20px; left: 50%;
            transform: translateX(-50%);
            width: 80px; height: 80px;
            background: radial-gradient(circle, rgba(255,255,255,0.06), transparent 70%);
            filter: blur(16px);
            pointer-events: none;
        }

        .brand-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px; height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg,
                rgba(255,255,255,0.08),
                rgba(255,255,255,0.03));
            border: 1px solid rgba(255,255,255,0.12);
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
            transition: var(--transition);
        }

        .brand-logo-wrap:hover {
            transform: scale(1.05);
            border-color: rgba(255,255,255,0.25);
        }

        .brand-logo {
            width: 36px; height: 36px;
            object-fit: contain;
            border-radius: 8px;
        }

        .sidebar-header h3 {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 2px;
            letter-spacing: -0.3px;
            background: linear-gradient(90deg, #e0eaff, #c7d7fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-header small {
            display: block;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.35);
            margin-top: 2px;
        }

        /* ── Sidebar menu ── */
        .sidebar-menu {
            list-style: none;
            padding: 16px 12px;
            flex: 1;
        }

        .sidebar-menu li {
            margin: 2px 0;
        }

        /* Section divider label */
        .menu-section-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.25);
            padding: 16px 12px 6px;
            display: block;
        }

        .sidebar-menu a,
        .sidebar-logout {
            color: rgba(255,255,255,0.65);
            padding: 10px 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 11px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 500;
            transition: all var(--transition);
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .sidebar-menu a::before,
        .sidebar-logout::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg,
                rgba(255,255,255,0.08),
                rgba(255,255,255,0.03));
            opacity: 0;
            border-radius: inherit;
            transition: opacity var(--transition);
        }

        .sidebar-menu a:hover,
        .sidebar-logout:hover {
            color: #fff;
            border-color: rgba(255,255,255,0.08);
        }

        .sidebar-menu a:hover::before,
        .sidebar-logout:hover::before {
            opacity: 1;
        }

        .sidebar-menu a i,
        .sidebar-logout i {
            width: 18px;
            text-align: center;
            font-size: 14px;
            flex-shrink: 0;
            transition: transform var(--transition);
        }

        .sidebar-menu a:hover i,
        .sidebar-logout:hover i {
            transform: translateX(2px);
        }

        .sidebar-menu a.active {
            color: #fff;
            background: linear-gradient(90deg,
                rgba(255,255,255,0.12),
                rgba(255,255,255,0.06));
            border-color: rgba(255,255,255,0.25);
            font-weight: 600;
        }

        .sidebar-menu a.active::after {
            content: '';
            position: absolute;
            left: 0; top: 20%; bottom: 20%;
            width: 3px;
            background: linear-gradient(180deg, #ffffff, #a1a1aa);
            border-radius: 0 2px 2px 0;
        }

        .sidebar-menu a.active i {
            color: #ffffff;
        }

        /* ── Sidebar logout button ── */
        .sidebar-logout {
            width: 100%;
            background: none;
            border: 1px solid transparent;
            cursor: pointer;
            text-align: left;
        }

        .sidebar-logout:hover {
            background: rgba(239,68,68,0.1);
            border-color: rgba(239,68,68,0.2);
            color: #fca5a5;
        }

        /* ── Sidebar divider ── */
        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin: 8px 12px;
        }

        /* ── Sidebar user pill ── */
        .sidebar-user {
            padding: 14px 16px;
            border-top: 1px solid rgba(255,255,255,0.07);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ════════════════════════════════════════
           MAIN CONTENT
        ════════════════════════════════════════ */
        .main-content {
            margin-left: var(--sidebar-w);
            width: calc(100% - var(--sidebar-w));
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ════════════════════════════════════════
           TOP NAVBAR
        ════════════════════════════════════════ */
        .navbar-top {
            background: #ffffff;
            padding: 0 28px;
            height: 64px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.07);
            border-bottom: 1px solid var(--border);
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
            gap: 16px;
            min-width: 0;
        }

        .navbar-top-right {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }

        /* ── Page title ── */
        .page-title {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .page-title-icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            background: linear-gradient(135deg, #f4f4f5, #e4e4e7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000000;
            font-size: 15px;
            flex-shrink: 0;
        }

        .page-title h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── User profile chip ── */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            border-radius: 40px;
            background: rgba(0,0,0,0.04);
            border: 1px solid var(--border);
            transition: var(--transition);
            cursor: default;
        }

        .user-profile:hover {
            background: rgba(0,0,0,0.06);
            border-color: rgba(0,0,0,0.15);
        }

        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #18181b, #71717a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .user-info h6 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
        }

        .user-info small {
            color: var(--text-muted);
            display: block;
            font-size: 11px;
        }

        /* ── Notification dot ── */
        .navbar-icon-btn {
            width: 36px; height: 36px;
            border-radius: 9px;
            background: rgba(0,0,0,0.04);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }
                                       
        .navbar-icon-btn:hover {
            background: #000000;
            color: #fff;
            border-color: #000000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* ════════════════════════════════════════
           CONTENT AREA
        ════════════════════════════════════════ */
        .content-area {
            padding: 28px;
            flex: 1;
            overflow-y: auto;
            min-width: 0;
            max-width: 100%;
            /* Subtle grid pattern */
            background-image:
                radial-gradient(circle at 1px 1px, rgba(0,0,0,0.04) 1px, transparent 0);
            background-size: 28px 28px;
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

        /* ════════════════════════════════════════
           ALERT STYLES
        ════════════════════════════════════════ */
        .alert {
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            border: 1px solid transparent;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            color: #065f46;
            border-color: #a7f3d0;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fff1f2, #ffe4e6);
            color: #9f1239;
            border-color: #fecdd3;
        }

        .alert-info {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #1e40af;
            border-color: #bfdbfe;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            color: #92400e;
            border-color: #fde68a;
        }

        /* ════════════════════════════════════════
           STAT CARDS
        ════════════════════════════════════════ */
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            border-top: none;
            transition: all var(--transition);
            height: 100%;
            min-width: 0;
            position: relative;
            overflow: hidden;
        }

        /* Colored top border via pseudo */
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-indigo));
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        /* Light watermark circle in corner */
        .stat-card::after {
            content: '';
            position: absolute;
            top: -20px; right: -20px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: radial-gradient(circle,
                rgba(0,0,0,0.06),
                transparent 70%);
            pointer-events: none;
        }

        .stat-card.primary::before { background: linear-gradient(90deg,#000000,#3f3f46); }
        .stat-card.info::before    { background: linear-gradient(90deg,#06b6d4,#0891b2); }
        .stat-card.success::before { background: linear-gradient(90deg,#10b981,#059669); }
        .stat-card.warning::before { background: linear-gradient(90deg,#f59e0b,#d97706); }
        .stat-card.danger::before  { background: linear-gradient(90deg,#ef4444,#dc2626); }
        .stat-card.purple::before  { background: linear-gradient(90deg,#8b5cf6,#7c3aed); }

        .stat-card.primary::after  { background: radial-gradient(circle,rgba(0,0,0,0.06),transparent 70%); }
        .stat-card.info::after     { background: radial-gradient(circle,rgba(6,182,212,0.08),transparent 70%); }
        .stat-card.success::after  { background: radial-gradient(circle,rgba(16,185,129,0.08),transparent 70%); }
        .stat-card.warning::after  { background: radial-gradient(circle,rgba(245,158,11,0.08),transparent 70%); }
        .stat-card.danger::after   { background: radial-gradient(circle,rgba(239,68,68,0.08),transparent 70%); }
        .stat-card.purple::after   { background: radial-gradient(circle,rgba(139,92,246,0.08),transparent 70%); }

        .stat-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .stat-card .stat-icon {
            font-size: 28px;
            margin-bottom: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px; height: 52px;
            border-radius: 13px;
        }

        .stat-card.primary  .stat-icon { color: #000000; background: #f4f4f5; }
        .stat-card.info     .stat-icon { color: #0891b2; background: #ecfeff; }
        .stat-card.success  .stat-icon { color: #059669; background: #ecfdf5; }
        .stat-card.warning  .stat-icon { color: #d97706; background: #fffbeb; }
        .stat-card.danger   .stat-icon { color: #dc2626; background: #fff1f2; }
        .stat-card.purple   .stat-icon { color: #7c3aed; background: #f5f3ff; }

        .stat-card .stat-value {
            font-size: 34px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 4px;
            line-height: 1;
            overflow-wrap: anywhere;
            letter-spacing: -1px;
        }

        .stat-card .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
        }

        /* ════════════════════════════════════════
           CARDS
        ════════════════════════════════════════ */
        .card {
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            background-color: var(--card-bg);
        }

        .card-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 16px 20px;
            font-weight: 600;
            font-size: 14px;
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

        /* ════════════════════════════════════════
           TABLE STYLES
        ════════════════════════════════════════ */
        .table {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            min-width: 0;
            margin-bottom: 0;
        }

        .table-responsive {
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: var(--radius-lg);
        }

        .table th,
        .table td {
            overflow-wrap: anywhere;
        }

        .table thead {
            background: linear-gradient(135deg, #f8faff, #eff3ff);
        }

        .table thead th {
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            padding: 14px 16px;
            border: none;
            border-bottom: 1px solid var(--border);
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.04);
            font-size: 13.5px;
        }

        .table tbody tr {
            transition: background var(--transition);
        }

        .table tbody tr:hover {
            background-color: rgba(59,130,246,0.03);
        }

        /* ════════════════════════════════════════
           FORM STYLES
        ════════════════════════════════════════ */
        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: var(--radius-sm);
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 14px;
            color: var(--text-main);
            transition: all var(--transition);
            background-color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #000000;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.08);
            outline: none;
        }

        /* ════════════════════════════════════════
           BUTTON STYLES
        ════════════════════════════════════════ */
        .btn {
            border-radius: var(--radius-sm);
            padding: 9px 18px;
            font-size: 13.5px;
            font-weight: 600;
            transition: all var(--transition);
            border: none;
            white-space: normal;
            letter-spacing: 0.1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #18181b, #000000);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #27272a, #18181b);
            color: #fff;
        }

        .btn-secondary {
            background-color: #e2e8f0;
            color: var(--text-main);
        }
        .btn-secondary:hover {
            background-color: #cbd5e1;
            color: var(--text-main);
        }

        .btn-danger {
            background-color: #ef4444;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239,68,68,0.35);
            color: #fff;
        }

        .btn-warning {
            background-color: #f59e0b;
            color: #fff;
        }
        .btn-warning:hover {
            background-color: #d97706;
            color: #fff;
        }

        .btn-info {
            background-color: #06b6d4;
            color: #fff;
        }
        .btn-info:hover {
            background-color: #0891b2;
            color: #fff;
        }

        .btn-success {
            background-color: #10b981;
            color: #fff;
        }
        .btn-success:hover {
            background-color: #059669;
            color: #fff;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-outline-secondary {
            border: 1px solid #e2e8f0;
            color: var(--text-muted);
            background: transparent;
        }
        .btn-outline-secondary:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        /* ════════════════════════════════════════
           BADGE STYLES
        ════════════════════════════════════════ */
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .badge-success, .bg-success {
            background-color: #dcfce7 !important;
            color: #166534 !important;
        }

        .badge-danger, .bg-danger {
            background-color: #fee2e2 !important;
            color: #991b1b !important;
        }

        .badge-warning, .bg-warning {
            background-color: #fef3c7 !important;
            color: #92400e !important;
        }

        .badge-info, .bg-info {
            background-color: #dbeafe !important;
            color: #1e40af !important;
        }

        /* ════════════════════════════════════════
           BREADCRUMB
        ════════════════════════════════════════ */
        .breadcrumb {
            background-color: transparent;
            margin-bottom: 20px;
            padding: 0;
            font-size: 13px;
        }

        .breadcrumb-item a { color: var(--accent-blue); }

        /* ════════════════════════════════════════
           EMPTY STATE
        ════════════════════════════════════════ */
        .empty-state {
            text-align: center;
            padding: 64px 20px;
        }

        .empty-state i {
            font-size: 56px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h4 { color: var(--text-muted); margin-bottom: 8px; }
        .empty-state p  { color: #94a3b8; }

        /* ════════════════════════════════════════
           LOADING SPINNER
        ════════════════════════════════════════ */
        .spinner {
            border: 3px solid #e2e8f0;
            border-top: 3px solid var(--accent-blue);
            border-radius: 50%;
            width: 36px; height: 36px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ════════════════════════════════════════
           PAGE ENTER ANIMATION
        ════════════════════════════════════════ */
        .content-area {
            animation: pageIn 0.35s cubic-bezier(.4,0,.2,1) both;
        }

        @keyframes pageIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        /* ════════════════════════════════════════
           RESPONSIVE
        ════════════════════════════════════════ */
        @media (max-width: 768px) {
            :root { --sidebar-w: 0px; }

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
                width: 272px;
                transform: translateX(0);
            }

            .content-area { padding: 16px; }

            .navbar-top {
                padding: 0 16px;
                flex-wrap: wrap;
                height: auto;
                gap: 10px;
                padding-top: 12px;
                padding-bottom: 12px;
            }

            .navbar-top-right {
                width: 100%;
                justify-content: flex-start;
            }

            .stat-card { padding: 18px; }
            .stat-card .stat-value { font-size: 26px; }
        }

        @media (max-width: 576px) {
            .content-area { padding: 12px; }

            .btn, .form-control, .form-select {
                width: 100%;
            }

            .table .btn,
            .card-header .btn,
            .list-group-item .btn {
                width: auto;
            }
        }

        /* ════════════════════════════════════════
           MOBILE TOGGLE
        ════════════════════════════════════════ */
        #sidebar-toggle {
            display: none;
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            width: 36px; height: 36px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
            transition: var(--transition);
        }

        #sidebar-toggle:hover {
            background: var(--accent-blue);
            color: #fff;
            border-color: var(--accent-blue);
        }

        @media (max-width: 768px) {
            #sidebar-toggle { display: flex; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand-logo-wrap">
                <img src="{{ asset('images/logo.png') }}"
                     alt="SafeTrack Logo"
                     class="brand-logo"
                     onerror="this.parentElement.innerHTML='<i class=\'fas fa-shield-alt\' style=\'font-size:24px;color:#ffffff;\'></i>'">
            </div>
            <h3>SafeTrack</h3>
            <small>Admin Dashboard</small>
        </div>

        @php
            $user = auth()->user();
            $canManageAccounts = $user?->canManageAccounts() ?? false;
            $canViewReports = ($user?->hasPermission('view_reports') ?? false) || ($user?->isSuperAdmin() ?? false);
            $canDelete = $user?->canDeleteHouseholds() ?? false;
        @endphp

        <ul class="sidebar-menu">
            <span class="menu-section-label">Main</span>

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
                <a href="{{ route('admin.residents.index') }}" class="@if(Request::routeIs('admin.residents.*')) active @endif">
                    <i class="fas fa-users"></i>
                    <span>Resident Management</span>
                </a>
            </li>

            @if($canManageAccounts || $canViewReports || $canDelete)
                <span class="menu-section-label">Administration</span>
            @endif


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
            @endif

            @if($canDelete)
                <li>
                    <a href="{{ route('admin.tokens.index') }}" class="@if(Request::routeIs('admin.tokens.*')) active @endif">
                        <i class="fas fa-key"></i>
                        <span>API Token Management</span>
                    </a>
                </li>
            @endif

            <span class="menu-section-label">Account</span>

            <li>
                <a href="{{ route('password.change') }}" class="@if(Request::routeIs('password.change')) active @endif">
                    <i class="fas fa-lock"></i>
                    <span>Change Password</span>
                </a>
            </li>

            <div class="sidebar-divider"></div>

            <li>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="sidebar-logout">
                        <i class="fas fa-arrow-right-from-bracket"></i>
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
                <button id="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')" aria-label="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">
                    <div class="page-title-icon">
                        @yield('page_icon')
                    </div>
                    <h4>@yield('page_title', 'Dashboard')</h4>
                </div>
            </div>

            <div class="navbar-top-right">
                <div class="user-profile">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <h6>{{ auth()->user()->name }}</h6>
                        <small>
                            @php
                                $roleLabel = auth()->user()->role->name
                                    ?? auth()->user()->normalizedRole();
                            @endphp
                            {{ $roleLabel ? ucfirst($roleLabel) : 'User' }}
                        </small>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alerts -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>There were some problems with your input:</strong>
                        <ul style="margin-top:6px; margin-bottom:0; padding-left:18px;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mt-1 flex-shrink-0"></i>
                    <div>{!! session('success') !!}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
                    <div>{!! session('error') !!}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                    <div>{!! session('info') !!}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('new_account'))
                @php $acct = session('new_account'); @endphp
                <div class="alert alert-dismissible fade show" role="alert" id="new-account-alert"
                     style="background:linear-gradient(135deg,#18181b,#09090b,#000000);color:white;
                            border-radius:14px;border:1px solid rgba(255,255,255,0.15);
                            box-shadow:0 8px 32px rgba(0,0,0,0.5);padding:0;">
                    <div style="padding:20px 24px;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                            <div style="width:42px;height:42px;border-radius:50%;
                                        background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);
                                        display:flex;align-items:center;justify-content:center;font-size:18px;color:#ffffff;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <strong style="font-size:15px;">Household Account Created Automatically</strong>
                                <div style="font-size:12px;opacity:.75;margin-top:2px;">
                                    Share these credentials with the household head. They must change their password on first login.
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
                            <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:12px 16px;">
                                <div style="font-size:11px;opacity:.55;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Username</div>
                                <div style="font-family:monospace;font-size:14px;font-weight:600;">{{ $acct['username'] }}</div>
                            </div>
                            <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:12px 16px;">
                                <div style="font-size:11px;opacity:.55;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Email / Login</div>
                                <div style="font-family:monospace;font-size:14px;font-weight:600;overflow-wrap:anywhere;">{{ $acct['email'] }}</div>
                            </div>
                            <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:12px 16px;border:1px solid rgba(252,211,77,.35);">
                                <div style="font-size:11px;opacity:.55;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Temporary Password</div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span id="tmp-pwd-display"
                                          style="font-family:monospace;font-size:15px;font-weight:700;color:#fcd34d;letter-spacing:1px;">
                                        {{ $acct['password'] }}
                                    </span>
                                    <button type="button" onclick="copyTmpPassword()" id="copy-pwd-btn"
                                            style="background:rgba(255,255,255,.15);border:none;border-radius:6px;
                                                   padding:4px 10px;color:white;cursor:pointer;font-size:11px;transition:.2s;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:10px;font-size:11px;opacity:.45;">
                            <i class="fas fa-clock"></i> This alert auto-closes in 30 seconds. Note these credentials before it closes.
                        </div>
                    </div>
                </div>
                <script>
                    function copyTmpPassword() {
                        var pwd = document.getElementById('tmp-pwd-display').innerText.trim();
                        navigator.clipboard.writeText(pwd).then(function() {
                            var btn = document.getElementById('copy-pwd-btn');
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            setTimeout(function() { btn.innerHTML = '<i class="fas fa-copy"></i>'; }, 2000);
                        });
                    }
                    setTimeout(function() {
                        var el = document.getElementById('new-account-alert');
                        if (el) { new bootstrap.Alert(el).close(); }
                    }, 30000);
                </script>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss regular alerts after 5 seconds
        document.querySelectorAll('.alert:not(#new-account-alert)').forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, 5000);
        });

        // Close sidebar overlay on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle  = document.getElementById('sidebar-toggle');
            if (window.innerWidth <= 768 &&
                sidebar.classList.contains('active') &&
                !sidebar.contains(e.target) &&
                !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

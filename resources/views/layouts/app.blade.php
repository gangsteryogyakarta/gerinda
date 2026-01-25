<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Gerindra Event Management</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <style>
        :root {
            /* Gerindra Red Theme - Light Mode */
            --primary: #DC2626;
            --primary-light: #FEE2E2;
            --primary-dark: #B91C1C;
            --primary-gradient: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            
            --accent: #F59E0B;
            --accent-light: #FEF3C7;
            
            --success: #10B981;
            --success-light: #D1FAE5;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --danger: #EF4444;
            --danger-light: #FEE2E2;
            --info: #3B82F6;
            --info-light: #DBEAFE;
            
            /* Glass Theme Colors */
            --bg-body: transparent;
            --bg-card: rgba(255, 255, 255, 0.1);
            --bg-sidebar: linear-gradient(135deg, rgba(220, 38, 38, 0.85) 0%, rgba(153, 27, 27, 0.95) 100%);
            --bg-input: rgba(0, 0, 0, 0.2);
            --bg-hover: rgba(255, 255, 255, 0.1);
            
            --text-primary: #FFFFFF;
            --text-secondary: rgba(255, 255, 255, 0.8);
            --text-muted: rgba(255, 255, 255, 0.6);
            --text-white: #FFFFFF;
            
            --border-color: rgba(255, 255, 255, 0.15);
            --border-light: rgba(255, 255, 255, 0.1);
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            
            --radius: 12px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --radius-full: 9999px;
            
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--text-primary); /* Fallback */
            color: var(--text-primary);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
            background-image: url('{{ asset("img/bg.jpg") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }

        .bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
            pointer-events: none;
        }

        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 50;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            position: fixed;
            top: 20px;
            left: 20px;
            bottom: 20px;
            border-radius: var(--radius-xl);
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-header {
            padding: 28px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
        }


        .sidebar-logo {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            color: white;
            backdrop-filter: blur(10px);
        }

        .sidebar-brand {
            flex: 1;
        }

        .sidebar-brand h1 {
            font-size: 18px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
        }

        .sidebar-brand span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0 16px;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: var(--radius-lg);
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }

        .nav-item i {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

            font-weight: 700;
            padding: 2px 8px;
            border-radius: var(--radius-full);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .sidebar-footer {
            padding: 20px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary);
        }

        .sidebar-user-info {
            flex: 1;
        }

        .sidebar-user-name {
            font-weight: 600;
            color: white;
            font-size: 13px;
        }

        .sidebar-user-role {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: calc(var(--sidebar-width) + 40px);
            padding: 24px 32px 24px 0;
            position: relative;
            z-index: 1;
        }

        /* Page Header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            gap: 24px;
        }

        .page-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-header-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .page-header p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .page-header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Search */
        .search-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 12px 20px;
            min-width: 320px;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(12px);
        }

        .search-box input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 14px;
            color: var(--text-primary);
            outline: none;
        }

        .search-box input::placeholder {
            color: var(--text-muted);
        }

        .search-box i {
            color: var(--text-muted);
            width: 20px;
            height: 20px;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        @media (max-width: 1400px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.primary {
            background: rgba(220, 38, 38, 0.2);
            color: #EF4444;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .stat-icon.success {
            background: var(--success-light);
            color: var(--success);
        }

        .stat-icon.warning {
            background: var(--warning-light);
            color: var(--warning);
        }

        .stat-icon.info {
            background: var(--info-light);
            color: var(--info);
        }

        .stat-icon i {
            width: 24px;
            height: 24px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .stat-trend.up {
            color: var(--success);
        }

        .stat-trend.down {
            color: var(--danger);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 4px;
            letter-spacing: -1px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .stat-progress {
            margin-top: 16px;
        }

        .stat-progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .stat-progress-fill {
            height: 100%;
            border-radius: var(--radius-full);
            background: var(--primary-gradient);
            transition: width 0.5s ease;
        }

        .stat-progress-fill.success {
            background: linear-gradient(90deg, #10B981, #059669);
        }

        .stat-progress-fill.warning {
            background: linear-gradient(90deg, #F59E0B, #D97706);
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-light);
            overflow: hidden;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-light);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-white);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title-icon {
            width: 32px;
            height: 32px;
            background: rgba(220, 38, 38, 0.15);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #EF4444;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .card-body {
            padding: 24px;
        }

        /* Grid Layouts */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .grid-3-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1200px) {
            .grid-2, .grid-3-1 {
                grid-template-columns: 1fr;
            }
        }

        /* Chart Container */
        .chart-container {
            height: 320px;
            position: relative;
        }

        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--bg-input);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-secondary);
            overflow: hidden;
        }

        .activity-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-info {
            flex: 1;
        }

        .activity-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .activity-desc {
            font-size: 13px;
            color: var(--text-muted);
        }

        .activity-value {
            text-align: right;
        }

        .activity-value-main {
            font-weight: 700;
            color: var(--text-primary);
        }

        .activity-value-sub {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: var(--radius-lg);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn i {
            width: 18px;
            height: 18px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 14px 0 rgba(220, 38, 38, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(220, 38, 38, 0.5);
        }

        .btn-secondary {
            background: var(--bg-input);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background: var(--border-color);
            color: var(--text-primary);
        }

        .btn-success {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-block {
            width: 100%;
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: var(--radius-full);
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        .badge-success {
            background: var(--success-light);
            color: var(--success);
        }

        .badge-warning {
            background: var(--warning-light);
            color: var(--warning);
        }

        .badge-info {
            background: var(--info-light);
            color: var(--info);
        }

        .badge-danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: left;
            padding: 14px 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--border-light);
        }

        .table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-primary);
        }

        .table tr:hover {
            background: var(--bg-hover);
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-label .required {
            color: var(--primary);
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .form-error {
            font-size: 12px;
            color: var(--danger);
            margin-top: 6px;
        }

        /* Map Container */
        .map-container {
            height: 400px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: var(--bg-input);
        }

        /* Progress */
        .progress {
            height: 8px;
            background: var(--bg-input);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: var(--radius-full);
            transition: width 0.5s ease;
        }

        .progress-bar.primary {
            background: var(--primary-gradient);
        }

        .progress-bar.success {
            background: linear-gradient(90deg, #10B981, #059669);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                border-radius: 0;
                transition: left 0.3s ease;
            }

            .sidebar.open {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-input);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

    </style>
    @stack('styles')
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo" style="background: transparent;">
                    <img src="{{ asset('img/logo-gerindra.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="sidebar-brand">
                    <h1>GERINDRA</h1>
                    <span>Event Management</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Overview</span>
                    </a>
                    <a href="{{ route('events.index') }}" class="nav-item {{ request()->routeIs('events.*') ? 'active' : '' }}">
                        <i data-lucide="calendar-days"></i>
                        <span>Events</span>
                        @php $activeEvents = \App\Models\Event::whereIn('status', ['published', 'ongoing'])->count(); @endphp
                        @if($activeEvents > 0)
                            <span class="nav-badge">{{ $activeEvents }}</span>
                        @endif
                    </a>
                    <a href="{{ route('massa.index') }}" class="nav-item {{ request()->routeIs('massa.*') ? 'active' : '' }}">
                        <i data-lucide="users"></i>
                        <span>Data Massa</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Operasional</div>
                    <a href="{{ route('checkin.index') }}" class="nav-item {{ request()->routeIs('checkin.*') ? 'active' : '' }}">
                        <i data-lucide="scan"></i>
                        <span>Check-in</span>
                    </a>
                    <a href="{{ route('lottery.index') }}" class="nav-item {{ request()->routeIs('lottery.*') ? 'active' : '' }}">
                        <i data-lucide="gift"></i>
                        <span>Undian Hadiah</span>
                    </a>
                    <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                        <i data-lucide="ticket"></i>
                        <span>Tiket</span>
                    </a>
                    <a href="{{ route('whatsapp.index') }}" class="nav-item {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                        <i data-lucide="message-circle"></i>
                        <span>WhatsApp Blast</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Analytics</div>
                    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i data-lucide="bar-chart-3"></i>
                        <span>Statistik</span>
                    </a>
                    <a href="{{ route('maps.index') }}" class="nav-item {{ request()->routeIs('maps.*') ? 'active' : '' }}">
                        <i data-lucide="map"></i>
                        <span>Peta Sebaran</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i data-lucide="settings"></i>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="{{ route('profile.index') }}" class="sidebar-user" title="Profil Saya">
                    <div class="sidebar-user-avatar">
                        {{ substr(auth()->user()?->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ Str::limit(auth()->user()?->name ?? 'Admin', 15) }}</div>
                        <div class="sidebar-user-role">{{ auth()->user()?->roles?->first()?->name ?? 'Guest' }}</div>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin-left: 8px;">
                    @csrf
                    <button type="submit" class="btn-icon-only" title="Logout" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 8px; border-radius: var(--radius);">
                        <i data-lucide="log-out" style="width: 18px; height: 18px;"></i>
                    </button>
                </form>
            </div>
            <style>
                .sidebar-footer {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 16px;
                    border-top: 1px solid rgba(255,255,255,0.1);
                    background: rgba(0,0,0,0.2);
                }
                .sidebar-user {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    flex: 1;
                    text-decoration: none;
                    color: inherit;
                    transition: opacity 0.2s;
                }
                .sidebar-user:hover {
                    opacity: 0.8;
                }
                .btn-icon-only:hover {
                    background: rgba(255,255,255,0.1) !important;
                    color: white !important;
                }
            </style>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success" style="background: var(--success-light); color: var(--success); padding: 16px 20px; border-radius: var(--radius-lg); margin-bottom: 24px; border-left: 4px solid var(--success);">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" style="background: var(--danger-light); color: var(--danger); padding: 16px 20px; border-radius: var(--radius-lg); margin-bottom: 24px; border-left: 4px solid var(--danger);">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>
</html>

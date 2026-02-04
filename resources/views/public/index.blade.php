<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Daftar Event Partai Gerindra DI Yogyakarta - Pendaftaran Peserta">
    <title>Daftar Event - Partai Gerindra DI Yogyakarta</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo-gerindra.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo-gerindra.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #DC2626;
            --primary-dark: #B91C1C;
            --primary-light: #FEE2E2;
            --secondary: #1E293B;
            --success: #10B981;
            --warning: #F59E0B;
            --info: #3B82F6;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-muted: #94A3B8;
            --bg-primary: #FFFFFF;
            --bg-secondary: #F8FAFC;
            --bg-tertiary: #F1F5F9;
            --border-light: #E2E8F0;
            --border-medium: #CBD5E1;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-image: url('{{ asset('img/bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--text-primary);
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }

        .header {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('{{ asset('img/banner.png') }}');
            background-size: cover;
            background-position: center;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 10;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .header-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .logo {
            width: 140px;
            height: 140px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--primary);
            font-weight: 800;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: relative;
            z-index: 2;
        }

        .login-btn-header {
            position: absolute;
            top: 2rem;
            right: 2rem;
            z-index: 10;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .login-btn-header:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
            letter-spacing: -0.5px;
        }

        .header .subtitle {
            font-size: 1.125rem;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            margin-top: 1rem;
            font-size: 0.875rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            position: relative;
            z-index: 1;
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 1.5rem;
            color: #ffffff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .section-title p {
            color: rgba(255, 255, 255, 0.85);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.2);
            color: #FCA5A5;
            border: 1px solid rgba(220, 38, 38, 0.4);
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.2);
            color: #93C5FD;
            border: 1px solid rgba(59, 130, 246, 0.4);
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .event-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .event-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.35);
        }

        .event-image {
            height: 160px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .event-image-icon {
            font-size: 3rem;
            color: rgba(255,255,255,0.3);
        }

        .event-date-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: white;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            text-align: center;
            box-shadow: var(--shadow-md);
        }

        .event-date-badge .day {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }

        .event-date-badge .month {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
        }

        .event-content {
            padding: 1.25rem;
        }

        .event-category {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(220, 38, 38, 0.2);
            color: #FCA5A5;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50px;
            margin-bottom: 0.75rem;
        }

        .event-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .event-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.75);
        }

        .event-meta-item svg {
            width: 16px;
            height: 16px;
            color: rgba(255, 255, 255, 0.5);
        }

        .event-quota {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
        }

        .quota-text {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.75);
        }

        .quota-value {
            font-weight: 600;
            color: #34D399;
        }

        .quota-bar {
            flex: 1;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            margin: 0 0.75rem;
            overflow: hidden;
        }

        .quota-fill {
            height: 100%;
            background: linear-gradient(90deg, #10B981 0%, #34D399 100%);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        }

        .btn-disabled {
            background: var(--border-medium);
            color: var(--text-muted);
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .empty-state-icon svg {
            width: 40px;
            height: 40px;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.75);
        }

        .footer {
            text-align: center;
            padding: 2rem;
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.875rem;
            position: relative;
            z-index: 1;
        }

        .footer a {
            color: #FCA5A5;
            text-decoration: none;
        }

        .footer a:hover {
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .header {
                padding: 3rem 1.5rem;
                background-position: center top;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="{{ route('login') }}" class="login-btn-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Login
        </a>
        <div class="header-content">
            <div class="logo">
                <img src="{{ asset('img/logo-gerindra.png') }}" alt="Logo Gerindra" style="width: 60%; height: auto;">
            </div>
            <h1>Sugeng Rawuh!</h1>
            <p class="subtitle">Situs Resmi DPD Partai Gerindra DIY</p>
            <!-- <div class="location-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                DI Yogyakarta
            </div> -->
        </div>
    </header>

    <main class="container">
        <div class="section-title">
            <h2>Kegiatan terdekat kami</h2>
            <p>Daftarkan Diri Anda Segera</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                {{ session('info') }}
            </div>
        @endif

        @if($events->count() > 0)
            <div class="events-grid">
                @foreach($events as $event)
                    @php
                        $registrationCount = $event->registrations()->count();
                        $quotaPercentage = $event->quota ? min(100, ($registrationCount / $event->quota) * 100) : 0;
                        $slotsLeft = $event->quota ? $event->quota - $registrationCount : null;
                    @endphp
                    <div class="event-card">
                        <div class="event-image">
                            @if($event->banner_image)
                                <img src="{{ asset('storage/' . $event->banner_image) }}" alt="{{ $event->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <span class="event-image-icon">🎪</span>
                            @endif
                            <div class="event-date-badge">
                                <div class="day">{{ $event->event_start->format('d') }}</div>
                                <div class="month">{{ $event->event_start->translatedFormat('M Y') }}</div>
                            </div>
                        </div>
                        <div class="event-content">
                            <span class="event-category">{{ $event->category->name ?? 'Event' }}</span>
                            <h3 class="event-title">{{ $event->name }}</h3>
                            
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $event->venue_name ?? 'DI Yogyakarta' }}
                                </div>
                                <div class="event-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ $event->event_start->format('H:i') }} WIB
                                </div>
                            </div>

                            @if($event->quota)
                                <div class="event-quota">
                                    <span class="quota-text">{{ $registrationCount }} terdaftar</span>
                                    <div class="quota-bar">
                                        <div class="quota-fill" style="width: {{ $quotaPercentage }}%"></div>
                                    </div>
                                    <span class="quota-value">{{ $slotsLeft > 0 ? $slotsLeft . ' slot' : 'Penuh' }}</span>
                                </div>
                            @endif

                            @if($slotsLeft === null || $slotsLeft > 0)
                                <a href="{{ route('public.register', $event) }}" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <line x1="19" y1="8" x2="19" y2="14"/>
                                        <line x1="22" y1="11" x2="16" y2="11"/>
                                    </svg>
                                    Daftar Sekarang
                                </a>
                            @else
                                <button class="btn btn-disabled" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                    Kuota Penuh
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                        <line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </div>
                <h3>Belum Ada Event Tersedia</h3>
                <p>Saat ini belum ada event yang dibuka untuk pendaftaran.<br>Silakan cek kembali nanti.</p>
            </div>
        @endif
    </main>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} DPD Partai Gerindra DI Yogyakarta. All rights reserved.</p>
        <p style="margin-top: 0.5rem;">
            <a href="{{ route('login') }}">Login Admin</a>
        </p>
    </footer>
</body>
</html>

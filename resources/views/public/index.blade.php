<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Daftar Event Partai Gerindra DI Yogyakarta - Pendaftaran Peserta">
    <title>Daftar Event - Partai Gerindra DI Yogyakarta</title>
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
            background: linear-gradient(180deg, #FEE2E2 0%, #FFFFFF 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
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
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: var(--primary);
            font-weight: 800;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 700;
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
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .section-title p {
            color: var(--text-secondary);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary);
        }

        .alert-info {
            background: #DBEAFE;
            color: #1E40AF;
            border: 1px solid var(--info);
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .event-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-light);
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
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
            background: var(--primary-light);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50px;
            margin-bottom: 0.75rem;
        }

        .event-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
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
            color: var(--text-secondary);
        }

        .event-meta-item svg {
            width: 16px;
            height: 16px;
            color: var(--text-muted);
        }

        .event-quota {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
        }

        .quota-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .quota-value {
            font-weight: 600;
            color: var(--success);
        }

        .quota-bar {
            flex: 1;
            height: 6px;
            background: var(--border-light);
            border-radius: 3px;
            margin: 0 0.75rem;
            overflow: hidden;
        }

        .quota-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--success) 0%, #34D399 100%);
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
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--border-medium);
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: var(--bg-tertiary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .empty-state-icon svg {
            width: 40px;
            height: 40px;
            color: var(--text-muted);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--text-secondary);
        }

        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
        }

        @media (max-width: 768px) {
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
        <div class="header-content">
            <div class="logo">G</div>
            <h1>Pendaftaran Event Partai Gerindra</h1>
            <p>Bergabunglah dalam kegiatan dan event kami</p>
            <div class="location-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                DI Yogyakarta
            </div>
        </div>
    </header>

    <main class="container">
        <div class="section-title">
            <h2>Event yang Tersedia</h2>
            <p>Pilih event yang ingin Anda ikuti dan lengkapi pendaftaran</p>
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
                            <span class="event-image-icon">🎪</span>
                            <div class="event-date-badge">
                                <div class="day">{{ $event->event_start->format('d') }}</div>
                                <div class="month">{{ $event->event_start->translatedFormat('M Y') }}</div>
                            </div>
                        </div>
                        <div class="event-content">
                            <span class="event-category">{{ $event->event_type ?? 'Event' }}</span>
                            <h3 class="event-title">{{ $event->nama_event }}</h3>
                            
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $event->lokasi ?? 'DI Yogyakarta' }}
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

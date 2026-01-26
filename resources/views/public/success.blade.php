<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pendaftaran Berhasil - Partai Gerindra DI Yogyakarta">
    <title>Pendaftaran Berhasil | Gerindra DIY</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #DC2626;
            --primary-dark: #B91C1C;
            --primary-light: #FEE2E2;
            --success: #10B981;
            --success-light: #D1FAE5;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-muted: #94A3B8;
            --bg-primary: #FFFFFF;
            --bg-secondary: #F8FAFC;
            --border-light: #E2E8F0;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 20px;
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
            background: linear-gradient(180deg, #D1FAE5 0%, #FFFFFF 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .success-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            text-align: center;
        }

        .success-header {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
            padding: 2.5rem 2rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            color: var(--success);
        }

        .success-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .success-header p {
            font-size: 0.9375rem;
            opacity: 0.9;
        }

        .success-body {
            padding: 2rem;
        }

        .registration-number {
            background: var(--bg-secondary);
            border: 2px dashed var(--border-light);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .registration-number-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .registration-number-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            font-family: 'Courier New', monospace;
        }

        .event-info {
            background: var(--primary-light);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .event-info-title {
            font-size: 0.75rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .event-info h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .event-info-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .event-info-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .event-info-item svg {
            width: 14px;
            height: 14px;
        }

        .participant-info {
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .participant-info-title {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .participant-info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.875rem;
        }

        .participant-info-row:last-child {
            border-bottom: none;
        }

        .participant-info-label {
            color: var(--text-secondary);
        }

        .participant-info-value {
            font-weight: 500;
            color: var(--text-primary);
        }

        .notice-box {
            background: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.8125rem;
            color: #92400E;
            text-align: left;
        }

        .notice-box strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: inherit;
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

        .footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.8125rem;
        }

        /* Confetti Animation */
        .confetti {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 1000;
        }

        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 20px;
            top: -20px;
            animation: confetti-fall 3s ease-out forwards;
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        @media (max-width: 480px) {
            .success-header {
                padding: 2rem 1.5rem;
            }

            .success-body {
                padding: 1.5rem;
            }

            .event-info-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="confetti" id="confetti"></div>

    <div class="container">
        <div class="success-card">
            <div class="success-header">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <h1>Pendaftaran Berhasil!</h1>
                <p>Anda telah terdaftar untuk mengikuti event</p>
            </div>

            <div class="success-body">
                <div class="registration-number">
                    <div class="registration-number-label">Nomor Registrasi</div>
                    <div class="registration-number-value">{{ $registration->ticket_number }}</div>
                    
                    @if($registration->qr_code_path)
                        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                            <img src="{{ asset('storage/' . $registration->qr_code_path) }}" alt="QR Code" style="width: 150px; height: 150px; border: 4px solid white; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        </div>
                    @endif
                </div>

                <div class="event-info">
                    <div class="event-info-title">Detail Event</div>
                    <h3>{{ $registration->event->nama_event }}</h3>
                    <div class="event-info-meta">
                        <div class="event-info-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                                <line x1="16" x2="16" y1="2" y2="6"/>
                                <line x1="8" x2="8" y1="2" y2="6"/>
                                <line x1="3" x2="21" y1="10" y2="10"/>
                            </svg>
                            {{ $registration->event->event_start->translatedFormat('d M Y') }}
                        </div>
                        <div class="event-info-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            {{ $registration->event->event_start->format('H:i') }} WIB
                        </div>
                        <div class="event-info-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $registration->event->lokasi ?? 'DI Yogyakarta' }}
                        </div>
                    </div>
                </div>

                <div class="participant-info">
                    <div class="participant-info-title">Data Peserta</div>
                    <div class="participant-info-row">
                        <span class="participant-info-label">Nama</span>
                        <span class="participant-info-value">{{ $registration->massa->nama_lengkap }}</span>
                    </div>
                    <div class="participant-info-row">
                        <span class="participant-info-label">NIK</span>
                        <span class="participant-info-value">{{ substr($registration->massa->nik, 0, 6) }}******{{ substr($registration->massa->nik, -4) }}</span>
                    </div>
                    <div class="participant-info-row">
                        <span class="participant-info-label">No. HP</span>
                        <span class="participant-info-value">{{ $registration->massa->no_hp }}</span>
                    </div>
                </div>

                <div class="notice-box">
                    <strong>📱 Penting!</strong>
                    Simpan nomor registrasi ini dan tunjukkan saat hadir di lokasi event. Anda juga akan menerima konfirmasi melalui WhatsApp.
                </div>

                <a href="{{ route('public.index') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} DPD Partai Gerindra DI Yogyakarta</p>
    </footer>

    <script>
        // Confetti effect
        function createConfetti() {
            const confettiContainer = document.getElementById('confetti');
            const colors = ['#DC2626', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899'];

            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti-piece';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                confettiContainer.appendChild(confetti);
            }

            // Remove confetti after animation
            setTimeout(() => {
                confettiContainer.innerHTML = '';
            }, 5000);
        }

        createConfetti();
    </script>
</body>
</html>

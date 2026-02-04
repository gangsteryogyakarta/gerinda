<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pendaftaran {{ $event->nama_event }} - Partai Gerindra DI Yogyakarta">
    <title>Daftar - {{ $event->nama_event }} | Gerindra DIY</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
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
            background: #F8FAFC;
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Dark Overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: var(--shadow-md);
        }

        .hero-banner {
            width: 100%;
            height: 300px;
            object-fit: cover;
            position: relative;
        }

        .hero-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 4rem 1rem 12rem;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            text-align: center;
        }

        .hero-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: background 0.2s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .header-info {
            flex: 1;
        }

        .header-info h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .header-info p {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .container {
            max-width: 800px;
            margin: -100px auto 0;
            padding: 0 1rem 3rem;
            position: relative;
            z-index: 10;
        }

        .event-summary {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
        }

        .event-summary-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .event-date-box {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            text-align: center;
            min-width: 70px;
        }

        .event-date-box .day {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .event-date-box .month {
            font-size: 0.7rem;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .event-details h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .event-meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .event-meta-item svg {
            width: 14px;
            height: 14px;
            color: var(--text-muted);
        }

        .form-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            overflow: hidden;
        }

        .form-section {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-light);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-section-title svg {
            width: 18px;
            height: 18px;
        }

        .form-grid {
            display: grid;
            gap: 1rem;
        }

        .form-grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .form-label .required {
            color: var(--primary);
        }

    /* Unified Input Styling */
        .form-input, 
        select.form-input {
            width: 100%;
            background-color: var(--bg-primary);
            border: 1px solid var(--border-medium);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-size: 0.9375rem;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        /* Fix for select elements */
        select.form-input {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px;
            height: 48px; /* Approx height of text input with padding */
        }

        .form-input:focus,
        select.form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }



        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }



        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .nik-status {
            display: none;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            padding: 0.25rem 0;
        }

        .nik-status.found {
            color: var(--success);
        }

        .nik-status.not-found {
            color: var(--text-muted);
        }

        .nik-status svg {
            width: 14px;
            height: 14px;
        }

        .gender-options {
            display: flex;
            gap: 1rem;
        }

        .gender-option {
            flex: 1;
        }

        .gender-option input {
            display: none;
        }

        .gender-option label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 2px solid var(--border-medium);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .gender-option input:checked + label {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
        }

        textarea.form-input {
            resize: vertical;
            min-height: 80px;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .alert-error {
            background: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary);
        }

        .alert svg {
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .form-actions {
            padding: 1.25rem;
            background: var(--bg-tertiary);
            display: flex;
            gap: 0.75rem;
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
        }

        .btn-primary {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        }

        .btn-primary:disabled {
            background: var(--border-medium);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: var(--bg-primary);
            color: var(--text-secondary);
            border: 1px solid var(--border-medium);
        }

        .btn-secondary:hover {
            background: var(--bg-tertiary);
        }

        .loading-spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .footer {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-secondary);
            font-size: 0.8125rem;
        }

        @media (max-width: 640px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr 1fr;
            }

            .event-summary-header {
                flex-direction: column;
                align-items: stretch;
            }

            .event-date-box {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
            }

            .hero-banner {
                height: 200px;
            }

            .container {
                margin-top: -60px;
            }
                height: 200px;
            }

            .container {
                margin-top: -60px;
            }
        }
        
        .event-description {
             color: var(--text-secondary);
             font-size: 1rem;
             line-height: 1.7;
             margin-top: 1.5rem;
        }

        .event-description p {
             margin-bottom: 1rem;
        }

        .event-description h1, .event-description h2, .event-description h3, .event-description h4 {
             color: var(--text-primary);
             font-weight: 800;
             margin-top: 2rem;
             margin-bottom: 1rem;
             line-height: 1.3;
        }

        .event-description ul, .event-description ol {
             margin-left: 1.5rem;
             margin-bottom: 1.5rem;
        }
        
        .event-description li {
            margin-bottom: 0.5rem;
        }

        .copywriting-text {
            border-top: 2px dashed var(--border-light);
            margin-top: 2rem;
            padding-top: 2rem;
            background: var(--bg-tertiary);
            padding: 2rem;
            border-radius: var(--radius-lg);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="{{ route('public.index') }}" class="back-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <div class="header-info" style="display: flex; align-items: center; gap: 12px;">
                <img src="{{ asset('img/logo-gerindra.png') }}" alt="Gerindra Logo" style="height: 48px; width: auto;">
                <div>
                    <h1>{{ $event->nama_event }}</h1>
                    <p>Form Pendaftaran Resmi</p>
                </div>
            </div>
        </div>
    </header>

    <div>
        @if($event->banner_image)
            <img src="{{ asset('storage/' . $event->banner_image) }}" class="hero-banner" alt="Banner Event">
        @else
            <div class="hero-banner" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);"></div>
        @endif
    </div>

    <main class="container">
        <div class="event-summary">
            <div class="event-summary-header">
                <div class="event-date-box">
                    <div class="day">{{ $event->event_start->format('d') }}</div>
                    <div class="month">{{ $event->event_start->translatedFormat('M Y') }}</div>
                </div>
                <div class="event-details">
                    <h2>{{ $event->nama_event }}</h2>
                    <div class="event-meta">
                        <div class="event-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $event->venue_name ?? 'Lokasi Event' }}
                        </div>
                        <div class="event-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            {{ $event->event_start->format('H:i') }} WIB
                        </div>
                    </div>
                </div>
            </div>

            @if($event->description || $event->copywriting)
                <div class="event-description">
                    @if($event->description)
                        <p>{{ $event->description }}</p>
                    @endif
                    
                    @if($event->copywriting)
                        <div class="copywriting-text">
                            {!! Illuminate\Support\Str::markdown($event->copywriting) !!}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <form id="registrationForm" action="{{ route('public.store', $event) }}" method="POST" class="form-card">
            @csrf

            {{-- Data KTP --}}
            <div class="form-section">
                <div class="form-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                        <line x1="2" x2="22" y1="10" y2="10"/>
                    </svg>
                    Data KTP
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">NIK <span class="required">*</span></label>
                        <input type="text" name="nik" id="nik" class="form-input" 
                            placeholder="Masukkan 16 digit NIK" 
                            maxlength="16" 
                            pattern="\d{16}"
                            value="{{ old('nik') }}" 
                            required>
                        <div id="nikStatus" class="nik-status">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span id="nikStatusText"></span>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Nama Lengkap (sesuai KTP) <span class="required">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-input" 
                            placeholder="Nama lengkap"
                            value="{{ old('nama_lengkap') }}" 
                            required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Kategori <span class="required">*</span></label>
                        <select name="kategori_massa" id="kategori_massa" class="form-input" required onchange="toggleSubKategori()">
                            <option value="Simpatisan" {{ old('kategori_massa') === 'Simpatisan' ? 'selected' : '' }}>Simpatisan</option>
                            <option value="Pengurus" {{ old('kategori_massa') === 'Pengurus' ? 'selected' : '' }}>Pengurus</option>
                        </select>
                    </div>

                    <div class="form-group full-width" id="sub_kategori_group" style="display: none;">
                        <label class="form-label">Sub Kategori <span class="required">*</span></label>
                        <select name="sub_kategori" id="sub_kategori" class="form-input">
                            <option value="">-- Pilih --</option>
                            <option value="DPD DIY" {{ old('sub_kategori') === 'DPD DIY' ? 'selected' : '' }}>DPD DIY</option>
                            <option value="DPC KABUPATEN" {{ old('sub_kategori') === 'DPC KABUPATEN' ? 'selected' : '' }}>DPC KABUPATEN</option>
                            <option value="PAC" {{ old('sub_kategori') === 'PAC' ? 'selected' : '' }}>PAC</option>
                        </select>
                    </div>

                    <script>
                        function toggleSubKategori() {
                            const kategori = document.getElementById('kategori_massa').value;
                            const subGroup = document.getElementById('sub_kategori_group');
                            const subInput = document.getElementById('sub_kategori');
                            
                            if (kategori === 'Pengurus') {
                                subGroup.style.display = 'block';
                                subInput.required = true;
                            } else {
                                subGroup.style.display = 'none';
                                subInput.required = false;
                                subInput.value = '';
                            }
                        }
                        
                        // Initialize on load
                        document.addEventListener('DOMContentLoaded', toggleSubKategori);
                    </script>

                    <div class="form-group full-width">
                        <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                        <div class="gender-options">
                            <div class="gender-option">
                                <input type="radio" name="jenis_kelamin" id="gender_l" value="L" {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }} required>
                                <label for="gender_l">👨 Laki-laki</label>
                            </div>
                            <div class="gender-option">
                                <input type="radio" name="jenis_kelamin" id="gender_p" value="P" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                                <label for="gender_p">👩 Perempuan</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-input" 
                            placeholder="Contoh: Yogyakarta"
                            value="{{ old('tempat_lahir', 'Yogyakarta') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-input"
                            value="{{ old('tanggal_lahir') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" id="pekerjaan" class="form-input" 
                            placeholder="Contoh: Wiraswasta"
                            value="{{ old('pekerjaan') }}">
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="form-section">
                <div class="form-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    Kontak
                </div>
                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp <span class="required">*</span></label>
                        <input type="tel" name="no_hp" id="no_hp" class="form-input" 
                            placeholder="08xxxxxxxxxx"
                            value="{{ old('no_hp') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-input" 
                            placeholder="email@example.com"
                            value="{{ old('email') }}">
                    </div>
                </div>
            </div>

            {{-- Alamat --}}
            <div class="form-section">
                <div class="form-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Alamat Domisili
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                        <textarea name="alamat" id="alamat" class="form-input" 
                            placeholder="Jalan, Gang, Dusun, dll"
                            required>{{ old('alamat') }}</textarea>
                    </div>

                    <div class="form-row full-width">
                        <div class="form-group">
                            <label class="form-label">RT</label>
                            <input type="text" name="rt" id="rt" class="form-input form-input-sm" 
                                placeholder="001"
                                maxlength="5"
                                value="{{ old('rt') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">RW</label>
                            <input type="text" name="rw" id="rw" class="form-input form-input-sm" 
                                placeholder="001"
                                maxlength="5"
                                value="{{ old('rw') }}">
                        </div>
                    </div>

                    <div class="full-width">
                        <x-location-selector 
                            :province-id="old('province_id', $defaultProvince?->id)"
                            :regency-id="old('regency_id')"
                            :district-id="old('district_id')"
                            :village-id="old('village_id')"
                            :postal-code="old('postal_code')"
                            :required="true"
                        />
                    </div>
                </div>
            </div>

            {{-- Lokasi di Peta --}}
            <div class="form-section">
                <div class="form-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                        <path d="M2 12h20"/>
                    </svg>
                    Lokasi di Peta (Opsional)
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    Klik tombol "Gunakan Lokasi Saya" atau klik langsung di peta untuk menandai lokasi rumah Anda. 
                    Ini membantu kami menampilkan sebaran massa secara akurat.
                </p>
                
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <button type="button" id="getLocationBtn" class="btn btn-secondary" style="flex: 1; padding: 0.625rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        Gunakan Lokasi Saya
                    </button>
                    <button type="button" id="clearLocationBtn" class="btn btn-secondary" style="padding: 0.625rem;" title="Hapus lokasi">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                
                <div id="locationMapContainer" style="height: 250px; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-light); position: relative;">
                    <div id="locationMap" style="height: 100%; width: 100%;"></div>
                    <div id="mapLoading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255,255,255,0.9); padding: 1rem 1.5rem; border-radius: var(--radius-sm); display: none;">
                        <span style="color: var(--text-secondary);">📍 Memuat peta...</span>
                    </div>
                </div>
                
                <div id="selectedCoords" style="margin-top: 0.75rem; padding: 0.625rem; background: var(--bg-tertiary); border-radius: var(--radius-sm); font-size: 0.8125rem; color: var(--text-secondary); display: none;">
                    📍 Koordinat: <span id="coordsDisplay">-</span>
                </div>
                
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                <input type="hidden" name="geocode_source" id="geocode_source" value="">
            </div>

            <div class="form-actions">
                <a href="{{ route('public.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span class="loading-spinner" id="loadingSpinner"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="submitIcon">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                    <span id="submitText">Daftar Sekarang</span>
                </button>
            </div>
        </form>
    </main>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} DPD Partai Gerindra DI Yogyakarta</p>
    </footer>

    <script>
        // NIK Check
        const nikInput = document.getElementById('nik');
        const nikStatus = document.getElementById('nikStatus');
        const nikStatusText = document.getElementById('nikStatusText');
        let nikTimeout;

        nikInput.addEventListener('input', function() {
            const nik = this.value.replace(/\D/g, '');
            this.value = nik;

            clearTimeout(nikTimeout);
            nikStatus.style.display = 'none';

            if (nik.length === 16) {
                nikTimeout = setTimeout(() => checkNik(nik), 500);
            }
        });

        async function checkNik(nik) {
            try {
                const response = await fetch(`{{ route('public.check-nik') }}?nik=${nik}`);
                const data = await response.json();

                nikStatus.style.display = 'flex';

                if (data.found) {
                    // Data found in local database
                    nikStatus.className = 'nik-status found';
                    nikStatusText.textContent = '✓ Data ditemukan - ' + data.data.nama_lengkap;

                    // Auto-fill form from local data
                    document.getElementById('nama_lengkap').value = data.data.nama_lengkap || '';
                    document.getElementById('no_hp').value = data.data.no_hp || '';
                    document.getElementById('email').value = data.data.email || '';
                    document.getElementById('tempat_lahir').value = data.data.tempat_lahir || '';
                    document.getElementById('tanggal_lahir').value = data.data.tanggal_lahir || '';
                    document.getElementById('alamat').value = data.data.alamat || '';

                    if (data.data.jenis_kelamin) {
                        document.getElementById('gender_' + data.data.jenis_kelamin.toLowerCase()).checked = true;
                    }
                } else if (data.parsed_data) {
                    // NIK parsed to extract embedded data
                    const parsed = data.parsed_data;
                    
                    nikStatus.className = 'nik-status found';
                    let statusText = '✓ NIK valid';
                    
                    if (parsed.is_yogyakarta) {
                        statusText += ' - ' + (parsed.regency_name || 'DI Yogyakarta');
                    } else if (parsed.province_name) {
                        statusText += ' - ' + parsed.province_name;
                    }
                    
                    nikStatusText.textContent = statusText;

                    // Auto-fill from parsed NIK data
                    if (parsed.tanggal_lahir) {
                        document.getElementById('tanggal_lahir').value = parsed.tanggal_lahir;
                    }
                    if (parsed.jenis_kelamin) {
                        document.getElementById('gender_' + parsed.jenis_kelamin.toLowerCase()).checked = true;
                    }
                } else if (data.error) {
                    nikStatus.className = 'nik-status not-found';
                    nikStatusText.textContent = '⚠ ' + data.error;
                } else {
                    nikStatus.className = 'nik-status not-found';
                    nikStatusText.textContent = 'Silakan lengkapi data';
                }
            } catch (error) {
                console.error('Error checking NIK:', error);
                nikStatus.style.display = 'none';
            }
        }



        // Form submission
        const form = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitIcon = document.getElementById('submitIcon');
        const submitText = document.getElementById('submitText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitIcon.style.display = 'none';
            loadingSpinner.style.display = 'block';
            submitText.textContent = 'Mendaftarkan...';
        });
    </script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Map Picker Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map
            const defaultLat = -7.7956;  // Yogyakarta
            const defaultLng = 110.3695;
            const defaultZoom = 12;
            
            const locationMap = L.map('locationMap').setView([defaultLat, defaultLng], defaultZoom);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(locationMap);
            
            let marker = null;
            
            // Function to set marker
            function setMarker(lat, lng, source) {
                // Remove existing marker
                if (marker) {
                    locationMap.removeLayer(marker);
                }
                
                // Add new marker
                marker = L.marker([lat, lng], { draggable: true }).addTo(locationMap);
                
                // Update form fields
                document.getElementById('latitude').value = lat.toFixed(8);
                document.getElementById('longitude').value = lng.toFixed(8);
                document.getElementById('geocode_source').value = source;
                
                // Show coordinates display
                document.getElementById('selectedCoords').style.display = 'block';
                document.getElementById('coordsDisplay').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                
                // Handle marker drag
                marker.on('dragend', function(e) {
                    const pos = e.target.getLatLng();
                    document.getElementById('latitude').value = pos.lat.toFixed(8);
                    document.getElementById('longitude').value = pos.lng.toFixed(8);
                    document.getElementById('geocode_source').value = 'user_input';
                    document.getElementById('coordsDisplay').textContent = `${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`;
                });
            }
            
            // Click on map to set marker
            locationMap.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng, 'user_input');
            });
            
            // Get current location button
            document.getElementById('getLocationBtn').addEventListener('click', function() {
                const btn = this;
                const originalText = btn.innerHTML;
                
                if (!navigator.geolocation) {
                    alert('Geolokasi tidak didukung oleh browser Anda');
                    return;
                }
                
                btn.disabled = true;
                btn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #ccc; border-top-color: #666; border-radius: 50%; animation: spin 0.8s linear infinite;"></span> Mencari lokasi...';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        setMarker(lat, lng, 'gps');
                        locationMap.setView([lat, lng], 17);
                        
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    },
                    function(error) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        
                        let message = 'Tidak dapat mengakses lokasi. ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                message += 'Izin lokasi ditolak.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message += 'Lokasi tidak tersedia.';
                                break;
                            case error.TIMEOUT:
                                message += 'Waktu permintaan habis.';
                                break;
                        }
                        alert(message);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                );
            });
            
            // Clear location button
            document.getElementById('clearLocationBtn').addEventListener('click', function() {
                if (marker) {
                    locationMap.removeLayer(marker);
                    marker = null;
                }
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                document.getElementById('geocode_source').value = '';
                document.getElementById('selectedCoords').style.display = 'none';
            });
            
            // If old values exist, restore marker
            const oldLat = document.getElementById('latitude').value;
            const oldLng = document.getElementById('longitude').value;
            if (oldLat && oldLng) {
                setMarker(parseFloat(oldLat), parseFloat(oldLng), 'user_input');
                locationMap.setView([parseFloat(oldLat), parseFloat(oldLng)], 15);
            }
            
            // Invalidate map size after a short delay (for hidden containers)
            setTimeout(function() {
                locationMap.invalidateSize();
            }, 500);
        });
    </script>
    @stack('scripts')
</body>
</html>

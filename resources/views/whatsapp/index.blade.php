@extends('layouts.app')

@section('title', 'WhatsApp Blast')

@section('content')
<div class="wa-blast-page">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
    </div>

    <!-- Hero Header -->
    <header class="wa-header">
        <div class="header-content">
            <div class="header-info">
                <div class="header-badge">
                    <i class="fab fa-whatsapp"></i>
                    <span>Messaging Platform</span>
                </div>
                <h1>WhatsApp Blast</h1>
                <p>Sistem Pengiriman Pesan Massal Terintegrasi</p>
            </div>
            <div id="connection-status-hero" class="connection-pill loading">
                <span class="pill-dot"></span>
                <span class="pill-text">Checking...</span>
            </div>
        </div>
    </header>

    <!-- Main Dashboard -->
    <main class="wa-dashboard">
        <!-- Stats Bar - Horizontal -->
        <section class="stats-bar">
            <div class="stat-item success">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-data">
                    <span class="stat-number" id="stat-sent">0</span>
                    <span class="stat-label">Terkirim</span>
                </div>
            </div>
            <div class="stat-item warning">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-data">
                    <span class="stat-number" id="stat-queue">0</span>
                    <span class="stat-label">Antrean</span>
                </div>
            </div>
            <div class="stat-item danger">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-data">
                    <span class="stat-number" id="stat-failed">0</span>
                    <span class="stat-label">Gagal</span>
                </div>
            </div>
        </section>

        <!-- Main Panels - Side by Side -->
        <section class="panels-grid">
            <!-- Left Panel: Connection Status -->
            <aside class="panel connection-panel">
                <div class="panel-header">
                    <div class="panel-icon"><i class="fas fa-link"></i></div>
                    <h3>Status Koneksi</h3>
                </div>
                <div class="panel-body">
                    <!-- Loading State -->
                    <div id="session-status" class="connection-state">
                        <div class="loader-ring"></div>
                        <p>Menghubungkan ke Server...</p>
                    </div>

                    <!-- QR Code -->
                    <div id="qr-container" class="connection-state d-none">
                        <div class="qr-box">
                            <img id="qr-image" src="" alt="QR Code">
                            <div class="qr-corners">
                                <span></span><span></span><span></span><span></span>
                            </div>
                        </div>
                        <p class="qr-hint"><i class="fas fa-mobile-alt"></i> Scan dengan WhatsApp</p>
                        <small class="qr-refresh"><i class="fas fa-sync-alt fa-spin"></i> Auto-refresh 5s</small>
                    </div>

                    <!-- Connected -->
                    <div id="connected-status" class="connection-state d-none">
                        <div class="success-check">
                            <i class="fas fa-check"></i>
                        </div>
                        <h4>Terhubung!</h4>
                        <p>WhatsApp siap digunakan</p>
                        <div class="phone-info">
                            <i class="fas fa-mobile-alt"></i>
                            <span id="connected-phone">--</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="connection-actions">
                        <button id="btn-start-session" class="btn-action btn-primary d-none" onclick="startSession()">
                            <i class="fas fa-play"></i> Mulai Sesi
                        </button>
                        <button id="btn-refresh-qr" class="btn-action btn-secondary d-none" onclick="refreshQR()">
                            <i class="fas fa-sync-alt"></i> Refresh QR
                        </button>
                        <button id="btn-logout" class="btn-action btn-danger d-none" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Putuskan
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Right Panel: Message Composer -->
            <article class="panel composer-panel">
                <div class="panel-header">
                    <nav class="tab-nav">
                        <button class="tab-btn active" data-tab="single">
                            <i class="fas fa-paper-plane"></i>
                            <span>Pesan Tunggal</span>
                        </button>
                        <button class="tab-btn" data-tab="bulk">
                            <i class="fas fa-rocket"></i>
                            <span>Blast Massa</span>
                        </button>
                        <button class="tab-btn" data-tab="event">
                            <i class="fas fa-calendar-check"></i>
                            <span>Event</span>
                        </button>
                    </nav>
                </div>
                <div class="panel-body">
                    <!-- Single Message Tab -->
                    <div class="tab-content active" id="tab-single">
                        <form id="single-message-form" class="message-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-phone"></i> Nomor WhatsApp</label>
                                    <input type="text" id="single-phone" placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-comment-dots"></i> Isi Pesan</label>
                                <textarea id="single-message" rows="4" placeholder="Ketik pesan..." required maxlength="4096"></textarea>
                                <span class="char-count"><span id="single-char-count">0</span>/4096</span>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn-send" id="btn-send-single">
                                    <i class="fab fa-whatsapp"></i> Kirim Sekarang
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Bulk Message Tab -->
                    <div class="tab-content" id="tab-bulk">
                        <form id="bulk-message-form" class="message-form">
                            <div class="form-row two-cols">
                                <div class="form-group">
                                    <label><i class="fas fa-filter"></i> Target</label>
                                    <select id="bulk-filter">
                                        <option value="all">Semua Massa</option>
                                        <option value="active">Massa Aktif</option>
                                        <option value="province">Per Provinsi</option>
                                    </select>
                                </div>
                                <div class="form-group" id="province-select-container" style="display:none;">
                                    <label><i class="fas fa-map-marker-alt"></i> Provinsi</label>
                                    <select id="bulk-province">
                                        <option value="">-- Pilih --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-hashtag"></i> Limit</label>
                                    <input type="number" id="bulk-limit" placeholder="1000" min="1" max="1000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-comment-dots"></i> Pesan Blast</label>
                                <textarea id="bulk-message" rows="4" placeholder="Gunakan {nama} untuk personalisasi" required maxlength="4096"></textarea>
                                <span class="char-count"><span id="bulk-char-count">0</span>/4096</span>
                            </div>
                            <div class="info-alert">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Sistem Antrean Cerdas</strong>
                                    <p>Pesan dikirim bertahap (delay 2-5 detik) untuk keamanan.</p>
                                </div>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn-send" id="btn-send-bulk">
                                    <i class="fas fa-rocket"></i> Mulai Blast
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Event Tab -->
                    <div class="tab-content" id="tab-event">
                        <form id="event-message-form" class="message-form">
                            <div class="form-row two-cols">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt"></i> Event</label>
                                    <select id="event-select" required>
                                        <option value="">-- Pilih Event --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-users"></i> Status Peserta</label>
                                    <select id="event-status">
                                        <option value="all">Semua</option>
                                        <option value="confirmed">Konfirmasi</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-bell"></i> Notifikasi</label>
                                <textarea id="event-message" rows="4" placeholder="Isi notifikasi..." required maxlength="4096"></textarea>
                                <span class="char-count"><span id="event-char-count">0</span>/4096</span>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn-send" id="btn-send-event">
                                    <i class="fas fa-bullhorn"></i> Kirim Notifikasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </article>
        </section>

        <!-- Templates Bar - Horizontal -->
        <section class="templates-bar">
            <h4><i class="fas fa-magic"></i> Template Cepat</h4>
            <div class="templates-list">
                <button class="template-chip" onclick="useTemplate('event-reminder')">
                    <i class="fas fa-calendar-alt"></i> Reminder Event
                </button>
                <button class="template-chip" onclick="useTemplate('ticket-confirm')">
                    <i class="fas fa-ticket-alt"></i> Konfirmasi Tiket
                </button>
                <button class="template-chip" onclick="useTemplate('general-blast')">
                    <i class="fas fa-bullhorn"></i> Info Umum
                </button>
                <button class="template-chip" onclick="useTemplate('thank-you')">
                    <i class="fas fa-heart"></i> Terima Kasih
                </button>
            </div>
        </section>
    </main>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content result-modal">
            <div class="modal-body">
                <div id="result-icon-container" class="result-icon"></div>
                <h5 id="result-title">Status</h5>
                <p id="result-message">Message</p>
                <button type="button" class="btn-action btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* === Variables === */
:root {
    --primary: #C52026;
    --primary-dark: #8B0000;
    --success: #10B981;
    --warning: #F59E0B;
    --danger: #EF4444;
    --dark: #1E293B;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-300: #CBD5E1;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --radius: 16px;
    --radius-sm: 10px;
    --shadow: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
}

.d-none { display: none !important; }

/* === Base === */
.wa-blast-page {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    background: var(--gray-50);
    position: relative;
}

/* === Animated Background === */
.animated-bg {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
}

.gradient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.3;
}

.orb-1 {
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, var(--primary) 0%, #FF6B6B 100%);
    top: -150px;
    right: -100px;
    animation: float 25s ease-in-out infinite;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, #3B82F6 0%, var(--primary) 100%);
    bottom: -100px;
    left: -100px;
    animation: float 20s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(30px, -30px); }
}

/* === Header === */
.wa-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    padding: 2rem 2rem 3rem;
    position: relative;
    z-index: 1;
}

.header-content {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.15);
    padding: 0.4rem 0.8rem;
    border-radius: 50px;
    color: white;
    font-size: 0.8rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.header-info h1 {
    color: white;
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
}

.header-info p {
    color: rgba(255,255,255,0.75);
    margin: 0.25rem 0 0;
    font-size: 0.95rem;
}

.connection-pill {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    color: white;
    font-weight: 500;
    font-size: 0.9rem;
}

.pill-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--warning);
    animation: pulse 2s infinite;
}

.connection-pill.connected .pill-dot { background: var(--success); }
.connection-pill.disconnected .pill-dot { background: var(--danger); }

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* === Dashboard === */
.wa-dashboard {
    max-width: 1400px;
    margin: -1.5rem auto 0;
    padding: 0 1.5rem 2rem;
    position: relative;
    z-index: 2;
}

/* === Stats Bar === */
.stats-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-item {
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-item.success .stat-icon { background: rgba(16,185,129,0.1); color: var(--success); }
.stat-item.warning .stat-icon { background: rgba(245,158,11,0.1); color: var(--warning); }
.stat-item.danger .stat-icon { background: rgba(239,68,68,0.1); color: var(--danger); }

.stat-data { display: flex; flex-direction: column; }
.stat-number { font-size: 1.5rem; font-weight: 800; color: var(--dark); }
.stat-label { font-size: 0.8rem; color: var(--gray-500); text-transform: uppercase; font-weight: 600; }

/* === Panels Grid === */
.panels-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.panel {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.panel-header {
    padding: 1.25rem;
    border-bottom: 1px solid var(--gray-100);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.panel-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.panel-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--dark);
}

.panel-body {
    padding: 1.5rem;
}

/* === Connection Panel === */
.connection-panel {
    min-height: 400px;
}

.connection-state {
    text-align: center;
    padding: 1rem 0;
}

.loader-ring {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    border: 3px solid var(--gray-200);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.connection-state p { color: var(--gray-500); margin: 0; }

/* QR Box */
.qr-box {
    width: 180px;
    height: 180px;
    margin: 0 auto 1rem;
    padding: 10px;
    background: white;
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow);
    position: relative;
}

.qr-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.qr-corners span {
    position: absolute;
    width: 16px;
    height: 16px;
    border: 3px solid var(--primary);
}

.qr-corners span:nth-child(1) { top: 0; left: 0; border-right: none; border-bottom: none; }
.qr-corners span:nth-child(2) { top: 0; right: 0; border-left: none; border-bottom: none; }
.qr-corners span:nth-child(3) { bottom: 0; left: 0; border-right: none; border-top: none; }
.qr-corners span:nth-child(4) { bottom: 0; right: 0; border-left: none; border-top: none; }

.qr-hint { color: var(--dark); font-weight: 500; margin: 0.5rem 0 0.25rem; }
.qr-refresh { color: var(--gray-400); font-size: 0.8rem; }

/* Success Check */
.success-check {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    box-shadow: 0 0 0 10px rgba(16,185,129,0.15);
    animation: success-pulse 2s infinite;
}

@keyframes success-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.3); }
    50% { box-shadow: 0 0 0 15px rgba(16,185,129,0); }
}

.connection-state h4 { color: var(--success); margin: 0 0 0.25rem; font-size: 1.25rem; }

.phone-info {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--gray-50);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    margin-top: 0.75rem;
}

.phone-info i { color: var(--primary); }
.phone-info span { font-weight: 600; color: var(--dark); }

/* Connection Actions */
.connection-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.btn-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-action.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
}

.btn-action.btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }

.btn-action.btn-secondary {
    background: var(--gray-100);
    color: var(--gray-600);
}

.btn-action.btn-secondary:hover { background: var(--gray-200); }

.btn-action.btn-danger {
    background: #FEF2F2;
    color: var(--danger);
    border: 1px solid #FECACA;
}

.btn-action.btn-danger:hover { background: #FEE2E2; }

/* === Composer Panel === */
.composer-panel .panel-header {
    padding: 0;
    border: none;
}

.tab-nav {
    display: flex;
    width: 100%;
    border-bottom: 1px solid var(--gray-100);
}

.tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    background: none;
    border: none;
    color: var(--gray-500);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
}

.tab-btn:hover { color: var(--primary); background: var(--gray-50); }

.tab-btn.active {
    color: var(--primary);
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary);
    border-radius: 3px 3px 0 0;
}

.tab-content { display: none; }
.tab-content.active { display: block; animation: fadeIn 0.3s; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* === Form Styles === */
.message-form { display: flex; flex-direction: column; gap: 1rem; }

.form-row { display: flex; gap: 1rem; }
.form-row.two-cols { flex-wrap: wrap; }
.form-row.two-cols .form-group { flex: 1; min-width: 200px; }

.form-group { display: flex; flex-direction: column; gap: 0.5rem; position: relative; }

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-600);
}

.form-group label i { color: var(--primary); font-size: 0.8rem; }

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: 0.95rem;
    font-family: inherit;
    transition: all 0.2s;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(197,32,38,0.1);
}

.form-group textarea { resize: vertical; min-height: 100px; }

.char-count {
    position: absolute;
    bottom: 8px;
    right: 12px;
    font-size: 0.75rem;
    color: var(--gray-400);
}

.info-alert {
    display: flex;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
    border-radius: var(--radius-sm);
    border: 1px solid #FCD34D;
}

.info-alert i { color: var(--warning); font-size: 1.1rem; margin-top: 2px; }
.info-alert strong { color: #92400E; display: block; font-size: 0.9rem; }
.info-alert p { color: #A16207; margin: 0.25rem 0 0; font-size: 0.85rem; }

.form-footer { display: flex; justify-content: flex-end; margin-top: 0.5rem; }

.btn-send {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(37,211,102,0.3);
}

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37,211,102,0.4);
}

/* === Templates Bar === */
.templates-bar {
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem;
    box-shadow: var(--shadow);
}

.templates-bar h4 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--gray-600);
}

.templates-bar h4 i { color: var(--primary); }

.templates-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.template-chip {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--gray-600);
    cursor: pointer;
    transition: all 0.2s;
}

.template-chip:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    transform: translateY(-1px);
}

.template-chip i { font-size: 0.9rem; }

/* === Result Modal === */
.result-modal {
    border: none;
    border-radius: var(--radius);
    overflow: hidden;
}

.result-modal .modal-body {
    padding: 2rem;
    text-align: center;
}

.result-icon { margin-bottom: 1rem; }
.result-icon i { font-size: 3.5rem; }

.result-modal h5 { font-weight: 700; margin-bottom: 0.5rem; }
.result-modal p { color: var(--gray-500); margin-bottom: 1.5rem; }

/* === Responsive === */
@media (max-width: 992px) {
    .panels-grid {
        grid-template-columns: 1fr;
    }
    
    .connection-panel {
        min-height: auto;
    }
}

@media (max-width: 768px) {
    .stats-bar {
        grid-template-columns: 1fr;
    }
    
    .header-info h1 { font-size: 1.5rem; }
    
    .tab-btn span { display: none; }
    
    .form-row.two-cols .form-group { min-width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
let qrRefreshInterval = null;
let statusCheckInterval = null;

const templates = {
    'event-reminder': `Halo Kader Gerindra! 🇮🇩\n\nJangan lupa hadir di acara:\n📅 {event}\n🗓️ {tanggal}\n📍 {lokasi}\n\nPastikan membawa KTP dan tiket registrasi.\n\nSalam Perjuangan! ✊\nDPD Gerindra DIY`,
    'ticket-confirm': `Selamat! 🎉\n\nRegistrasi Anda telah dikonfirmasi:\n📌 Event: {event}\n🎫 No. Tiket: {ticket}\n🗓️ Tanggal: {tanggal}\n\nTunjukkan pesan ini saat registrasi ulang.\n\nTerima kasih!\nDPD Gerindra DIY`,
    'general-blast': `Salam Perjuangan! 🇮🇩\n\nKepada seluruh kader Gerindra DIY,\n\n{isi_pesan}\n\nTerima kasih atas perhatiannya.\n\nMaju Terus Pantang Mundur! ✊\nDPD Gerindra DIY`,
    'thank-you': `Terima kasih! 🙏\n\nKami mengucapkan terima kasih atas kehadiran Anda di:\n📌 {event}\n\nPartisipasi Anda sangat berarti bagi perjuangan kita bersama.\n\nSampai jumpa di acara berikutnya!\n\nSalam Perjuangan! ✊\nDPD Gerindra DIY`
};

document.addEventListener('DOMContentLoaded', function() {
    checkHealth();
    loadProvinces();
    loadEvents();
    setupCharCounters();
    setupFormHandlers();
    setupTabs();
    statusCheckInterval = setInterval(checkHealth, 5000);
});

function setupTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('tab-' + this.dataset.tab).classList.add('active');
        });
    });
}

function checkHealth() {
    fetch('/whatsapp/health')
        .then(r => r.json())
        .then(data => updateConnectionStatus(data))
        .catch(() => showDisconnected('Server Offline'));
}

function updateConnectionStatus(data) {
    const heroPill = document.getElementById('connection-status-hero');
    const sessionStatus = document.getElementById('session-status');
    const qrContainer = document.getElementById('qr-container');
    const connectedStatus = document.getElementById('connected-status');
    const btnStart = document.getElementById('btn-start-session');
    const btnRefresh = document.getElementById('btn-refresh-qr');
    const btnLogout = document.getElementById('btn-logout');

    const status = data.status || {};
    const sessionState = status.status || 'disconnected';
    const isConnected = status.connected || false;

    sessionStatus.classList.add('d-none');
    qrContainer.classList.add('d-none');
    connectedStatus.classList.add('d-none');
    btnStart.classList.add('d-none');
    btnRefresh.classList.add('d-none');
    btnLogout.classList.add('d-none');

    if (isConnected) {
        heroPill.className = 'connection-pill connected';
        heroPill.innerHTML = '<span class="pill-dot"></span><span class="pill-text">Terhubung</span>';
        connectedStatus.classList.remove('d-none');
        btnLogout.classList.remove('d-none');
        document.getElementById('connected-phone').innerText = status.user?.id?.split(':')[0] || 'Unknown';
        stopQrRefresh();
    } else if (sessionState === 'qr') {
        heroPill.className = 'connection-pill';
        heroPill.innerHTML = '<span class="pill-dot"></span><span class="pill-text">Menunggu Scan</span>';
        qrContainer.classList.remove('d-none');
        btnRefresh.classList.remove('d-none');
        refreshQR();
        startQrRefresh();
    } else {
        heroPill.className = 'connection-pill disconnected';
        heroPill.innerHTML = '<span class="pill-dot"></span><span class="pill-text">Terputus</span>';
        sessionStatus.classList.remove('d-none');
        btnStart.classList.remove('d-none');
    }
}

function showDisconnected(msg) {
    const heroPill = document.getElementById('connection-status-hero');
    heroPill.className = 'connection-pill disconnected';
    heroPill.innerHTML = `<span class="pill-dot"></span><span class="pill-text">${msg}</span>`;
}

function startQrRefresh() { if (!qrRefreshInterval) qrRefreshInterval = setInterval(refreshQR, 5000); }
function stopQrRefresh() { if (qrRefreshInterval) { clearInterval(qrRefreshInterval); qrRefreshInterval = null; } }

function startSession() {
    const btn = document.getElementById('btn-start-session');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memulai...';
    fetch('/whatsapp/session/start', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} })
        .then(() => checkHealth())
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-play"></i> Mulai Sesi'; });
}

function refreshQR() {
    console.log('Fetching QR Code...');
    fetch('/whatsapp/qr')
        .then(r => r.json())
        .then(data => {
            console.log('QR Response:', data);
            if (data.success && data.qr) {
                const img = document.getElementById('qr-image');
                img.src = data.qr;
                img.onload = () => console.log('QR Image Loaded');
                img.onerror = (e) => console.error('QR Image Error', e);
            }
        })
        .catch(err => console.error('Fetch QR Error:', err));
}

function logout() {
    if (!confirm('Yakin ingin memutus koneksi WhatsApp?')) return;
    fetch('/whatsapp/logout', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} }).then(() => checkHealth());
}

function loadProvinces() {
    fetch('/api/v1/provinces').then(r => r.json()).then(data => {
        const select = document.getElementById('bulk-province');
        (data.data || data).forEach(p => select.innerHTML += `<option value="${p.id}">${p.name}</option>`);
    });
}

function loadEvents() {
    fetch('/api/v1/events?status=published,ongoing').then(r => r.json()).then(data => {
        const select = document.getElementById('event-select');
        (data.data || data).forEach(e => select.innerHTML += `<option value="${e.id}">${e.name}</option>`);
    });
}

function setupCharCounters() {
    ['single', 'bulk', 'event'].forEach(prefix => {
        const textarea = document.getElementById(`${prefix}-message`);
        const counter = document.getElementById(`${prefix}-char-count`);
        if (textarea && counter) textarea.addEventListener('input', () => counter.textContent = textarea.value.length);
    });
}

function setupFormHandlers() {
    document.getElementById('bulk-filter').addEventListener('change', function() {
        document.getElementById('province-select-container').style.display = this.value === 'province' ? 'block' : 'none';
    });

    const handleSend = (formId, url, btnId) => {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById(btnId);
            const originalText = btn.innerHTML;
            let payload = {};
            
            if (formId === 'single-message-form') {
                payload = { phone: document.getElementById('single-phone').value, message: document.getElementById('single-message').value };
            } else if (formId === 'bulk-message-form') {
                payload = { filter: document.getElementById('bulk-filter').value, province_id: document.getElementById('bulk-province').value, limit: document.getElementById('bulk-limit').value, message: document.getElementById('bulk-message').value };
            } else if (formId === 'event-message-form') {
                const eventId = document.getElementById('event-select').value;
                payload = { status: document.getElementById('event-status').value, message: document.getElementById('event-message').value };
                url = `/whatsapp/event/${eventId}/notify`;
            }

            if (!confirm('Lanjutkan pengiriman pesan?')) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Mengirim...';

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => showResult(data.success, data.message || (data.success ? 'Berhasil dikirim' : 'Gagal mengirim')))
            .finally(() => { btn.disabled = false; btn.innerHTML = originalText; });
        });
    };

    handleSend('single-message-form', '/whatsapp/send', 'btn-send-single');
    handleSend('bulk-message-form', '/whatsapp/blast', 'btn-send-bulk');
    handleSend('event-message-form', '', 'btn-send-event');
}

function showResult(success, message) {
    const modal = new bootstrap.Modal(document.getElementById('resultModal'));
    document.getElementById('result-title').innerText = success ? 'Berhasil!' : 'Gagal';
    document.getElementById('result-title').className = success ? 'text-success' : 'text-danger';
    document.getElementById('result-message').innerText = message;
    document.getElementById('result-icon-container').innerHTML = success 
        ? '<i class="fas fa-check-circle text-success"></i>' 
        : '<i class="fas fa-times-circle text-danger"></i>';
    modal.show();
}

function useTemplate(key) {
    const text = templates[key];
    const activePane = document.querySelector('.tab-content.active');
    const textarea = activePane?.querySelector('textarea');
    if (text && textarea) { textarea.value = text; textarea.dispatchEvent(new Event('input')); }
}
</script>
@endpush

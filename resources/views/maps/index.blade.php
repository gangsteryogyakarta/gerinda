@extends('layouts.app')

@section('title', 'Peta Sebaran Massa')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<style>
    /* ===== RESET & VARIABLES ===== */
    :root {
        --map-primary: #DC2626;
        --map-success: #10B981;
        --map-warning: #F59E0B;
        --map-info: #3B82F6;
        --map-dark: #1a1a1a;
        --map-light: #f8fafc;
        --map-border: #334155;
        --map-text: #e2e8f0;
        --map-muted: #94a3b8;
    }
    
    /* ===== PAGE LAYOUT ===== */
    .map-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 0;
    }
    
    .map-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        padding: 16px 20px;
        background: var(--map-dark);
        border-radius: 12px;
        border: 1px solid var(--map-border);
    }
    
    .map-page-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .map-page-title-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--map-success), #059669);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .map-page-title h1 {
        font-size: 20px;
        font-weight: 700;
        color: var(--map-text);
        margin: 0;
    }
    
    .map-page-title p {
        font-size: 12px;
        color: var(--map-muted);
        margin: 4px 0 0 0;
    }
    
    .map-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .map-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .map-btn-primary {
        background: var(--map-primary);
        color: white;
    }
    
    .map-btn-primary:hover {
        background: #b91c1c;
        transform: translateY(-1px);
    }
    
    .map-btn-secondary {
        background: #374151;
        color: var(--map-text);
        border: 1px solid var(--map-border);
    }
    
    .map-btn-secondary:hover {
        background: #4b5563;
    }
    
    /* ===== STATS ROW ===== */
    .map-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
    }
    
    .map-stat-card {
        background: var(--map-dark);
        border: 1px solid var(--map-border);
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .map-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .map-stat-icon.primary { background: rgba(220, 38, 38, 0.15); color: var(--map-primary); }
    .map-stat-icon.success { background: rgba(16, 185, 129, 0.15); color: var(--map-success); }
    .map-stat-icon.warning { background: rgba(245, 158, 11, 0.15); color: var(--map-warning); }
    .map-stat-icon.info { background: rgba(59, 130, 246, 0.15); color: var(--map-info); }
    
    .map-stat-content {
        flex: 1;
    }
    
    .map-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--map-text);
    }
    
    .map-stat-label {
        font-size: 12px;
        color: var(--map-muted);
        margin-top: 2px;
    }
    
    /* ===== MAP CONTAINER ===== */
    .map-main-container {
        background: var(--map-dark);
        border: 1px solid var(--map-border);
        border-radius: 12px;
        overflow: hidden;
    }
    
    .map-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #111827;
        border-bottom: 1px solid var(--map-border);
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .map-toolbar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .map-toolbar-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: var(--map-text);
    }
    
    .map-toolbar-title-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: rgba(16, 185, 129, 0.2);
        color: var(--map-success);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .map-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .map-select {
        padding: 8px 12px;
        font-size: 12px;
        background: #1f2937;
        border: 1px solid var(--map-border);
        border-radius: 6px;
        color: var(--map-text);
        cursor: pointer;
        min-width: 140px;
    }
    
    .map-select:focus {
        outline: 2px solid var(--map-primary);
        outline-offset: 2px;
    }
    
    .map-select option {
        background: #1f2937;
        color: var(--map-text);
    }
    
    #map-container {
        height: 550px;
        width: 100%;
        background: #374151;
        position: relative;
    }
    
    /* Loading Overlay */
    .map-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        color: white;
        gap: 12px;
    }
    
    .map-loading.hidden {
        display: none;
    }
    
    .map-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(255,255,255,0.2);
        border-top-color: var(--map-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Marker Counter Badge */
    .map-marker-count {
        position: absolute;
        bottom: 16px;
        left: 16px;
        background: var(--map-dark);
        border: 1px solid var(--map-border);
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        color: var(--map-text);
        z-index: 999;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .map-marker-count strong {
        color: var(--map-primary);
        font-size: 16px;
    }
    
    /* ===== LEAFLET OVERRIDES ===== */
    .leaflet-container {
        font-family: 'Plus Jakarta Sans', -apple-system, sans-serif !important;
        background: #374151 !important;
    }
    
    .leaflet-control-zoom {
        border: none !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    }
    
    .leaflet-control-zoom a {
        background: var(--map-dark) !important;
        color: var(--map-text) !important;
        border: 1px solid var(--map-border) !important;
        width: 36px !important;
        height: 36px !important;
        line-height: 36px !important;
        font-size: 18px !important;
    }
    
    .leaflet-control-zoom a:hover {
        background: #374151 !important;
    }
    
    .leaflet-popup-content-wrapper {
        background: var(--map-dark) !important;
        color: var(--map-text) !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.4) !important;
        border: 1px solid var(--map-border) !important;
        padding: 0 !important;
    }
    
    .leaflet-popup-content {
        margin: 0 !important;
        min-width: 220px;
    }
    
    .leaflet-popup-tip {
        background: var(--map-dark) !important;
        border: 1px solid var(--map-border) !important;
    }
    
    .leaflet-popup-close-button {
        color: var(--map-muted) !important;
        font-size: 20px !important;
        padding: 8px !important;
    }
    
    .leaflet-popup-close-button:hover {
        color: var(--map-text) !important;
    }
    
    /* Custom Popup Styles */
    .popup-content {
        padding: 16px;
    }
    
    .popup-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--map-border);
    }
    
    .popup-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--map-primary), #b91c1c);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
    }
    
    .popup-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--map-text);
    }
    
    .popup-id {
        font-size: 11px;
        color: var(--map-muted);
    }
    
    .popup-detail {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 12px;
    }
    
    .popup-detail-icon {
        color: var(--map-muted);
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .popup-detail-text {
        color: var(--map-text);
        line-height: 1.4;
    }
    
    .popup-coords {
        background: #1f2937;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 11px;
        color: var(--map-muted);
        font-family: monospace;
        margin-top: 12px;
    }
    
    /* Tooltip */
    .leaflet-tooltip {
        background: var(--map-dark) !important;
        color: var(--map-text) !important;
        border: 1px solid var(--map-border) !important;
        border-radius: 6px !important;
        padding: 6px 10px !important;
        font-size: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    }
    
    .leaflet-tooltip-left::before {
        border-left-color: var(--map-dark) !important;
    }
    
    .leaflet-tooltip-right::before {
        border-right-color: var(--map-dark) !important;
    }
    
    /* Custom Marker */
    .custom-marker-pin {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--map-primary);
        border: 3px solid white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.4);
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .custom-marker-pin:hover {
        transform: scale(1.2);
    }
    
    /* Cluster Markers */
    .marker-cluster-custom {
        background: transparent !important;
    }
    
    .cluster-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: white;
        font-weight: 700;
        font-size: 13px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        transition: transform 0.2s ease;
    }
    
    .cluster-icon:hover {
        transform: scale(1.1);
    }
    
    .cluster-small {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--map-primary), #b91c1c);
    }
    
    .cluster-medium {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--map-warning), #d97706);
    }
    
    .cluster-large {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, var(--map-success), #059669);
    }
    
    /* ===== INFO PANEL ===== */
    .map-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .map-info-card {
        background: var(--map-dark);
        border: 1px solid var(--map-border);
        border-radius: 12px;
        overflow: hidden;
    }
    
    .map-info-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        background: #111827;
        border-bottom: 1px solid var(--map-border);
    }
    
    .map-info-card-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .map-info-card-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--map-text);
    }
    
    .map-info-card-body {
        padding: 16px;
    }
    
    /* Legend Items */
    .legend-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    
    .legend-item:last-child {
        margin-bottom: 0;
    }
    
    .legend-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 10px;
        font-weight: 700;
    }
    
    .legend-text {
        font-size: 13px;
        color: var(--map-text);
    }
    
    /* Province Stats */
    .province-stat {
        margin-bottom: 14px;
    }
    
    .province-stat:last-child {
        margin-bottom: 0;
    }
    
    .province-stat-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }
    
    .province-stat-name {
        font-size: 12px;
        font-weight: 500;
        color: var(--map-text);
    }
    
    .province-stat-value {
        font-size: 12px;
        font-weight: 700;
        color: var(--map-primary);
    }
    
    .province-stat-bar {
        height: 6px;
        background: #374151;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .province-stat-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--map-primary), #ef4444);
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    
    /* ===== ACCESSIBILITY ===== */
    .map-select:focus-visible,
    .map-btn:focus-visible {
        outline: 2px solid var(--map-info);
        outline-offset: 2px;
    }
    
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }
    
    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .map-page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .map-actions {
            width: 100%;
        }
        
        .map-btn {
            flex: 1;
            justify-content: center;
        }
        
        #map-container {
            height: 400px;
        }
        
        .map-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .map-toolbar-right {
            width: 100%;
        }
        
        .map-select {
            flex: 1;
        }
        
        .map-info-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 480px) {
        .map-stats {
            grid-template-columns: 1fr;
        }
        
        #map-container {
            height: 350px;
        }
        
        .map-page-title h1 {
            font-size: 16px;
        }
    }
</style>
@endpush

@section('content')
<div class="map-page" role="main" aria-label="Peta Sebaran Massa">
    <!-- Page Header -->
    <header class="map-page-header">
        <div class="map-page-title">
            <div class="map-page-title-icon" aria-hidden="true">
                <i data-lucide="map" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h1>Peta Sebaran Massa</h1>
                <p>Visualisasi distribusi massa wilayah Yogyakarta & sekitarnya</p>
            </div>
        </div>
        <div class="map-actions">
            <button type="button" class="map-btn map-btn-secondary" onclick="toggleHeatmap()" aria-label="Toggle mode heatmap">
                <i data-lucide="flame" style="width: 16px; height: 16px;"></i>
                <span>Heatmap</span>
            </button>
            <button type="button" class="map-btn map-btn-primary" onclick="refreshMapData()" aria-label="Refresh data peta">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
                <span>Refresh</span>
            </button>
        </div>
    </header>
    
    <!-- Stats Row -->
    <section class="map-stats" aria-label="Statistik Massa">
        <div class="map-stat-card">
            <div class="map-stat-icon primary" aria-hidden="true">
                <i data-lucide="users" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="map-stat-content">
                <div class="map-stat-value">{{ number_format($stats['total_massa']) }}</div>
                <div class="map-stat-label">Total Massa</div>
            </div>
        </div>
        <div class="map-stat-card">
            <div class="map-stat-icon success" aria-hidden="true">
                <i data-lucide="map-pin" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="map-stat-content">
                <div class="map-stat-value" id="stat-visible">0</div>
                <div class="map-stat-label">Terlihat di Peta</div>
            </div>
        </div>
        <div class="map-stat-card">
            <div class="map-stat-icon warning" aria-hidden="true">
                <i data-lucide="globe" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="map-stat-content">
                <div class="map-stat-value">{{ $stats['provinces_covered'] }}</div>
                <div class="map-stat-label">Provinsi Tercakup</div>
            </div>
        </div>
        <div class="map-stat-card">
            <div class="map-stat-icon info" aria-hidden="true">
                <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
            </div>
            <div class="map-stat-content">
                <div class="map-stat-value">{{ $stats['total_events'] }}</div>
                <div class="map-stat-label">Total Event</div>
            </div>
        </div>
    </section>
    
    <!-- Main Map Container -->
    <section class="map-main-container" aria-label="Peta Interaktif">
        <div class="map-toolbar">
            <div class="map-toolbar-left">
                <div class="map-toolbar-title">
                    <div class="map-toolbar-title-icon" aria-hidden="true" style="background: rgba(220, 38, 38, 0.2); color: var(--map-primary);">
                        <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                    </div>
                    <span>Wilayah D.I. Yogyakarta</span>
                </div>
            </div>
            <div class="map-toolbar-right">
                <!-- Fixed Province Filter -->
                <div class="map-select" style="background: rgba(220, 38, 38, 0.1); border-color: var(--map-primary); color: #ef4444; cursor: not-allowed; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="lock" style="width: 12px; height: 12px;"></i>
                    D.I. Yogyakarta
                </div>
                <input type="hidden" id="filter-province" value="{{ \App\Models\Province::where('name', 'LIKE', '%YOGYAKARTA%')->first()?->id }}">
                
                <label for="filter-regency" class="sr-only">Filter Kabupaten</label>
                <select id="filter-regency" class="map-select" aria-label="Filter berdasarkan kabupaten">
                    <option value="">Semua Kabupaten</option>
                </select>
                
                <label for="map-layer" class="sr-only">Pilih Layer Peta</label>
                <select id="map-layer" class="map-select" aria-label="Pilih jenis tampilan peta">
                    <option value="markers">Marker Cluster</option>
                    <option value="heatmap">Heatmap</option>
                </select>
            </div>
        </div>
        
        <div id="map-container" role="application" aria-label="Peta Leaflet Interaktif" tabindex="0">
            <div class="map-loading" id="map-loading">
                <div class="map-spinner"></div>
                <span>Memuat data peta...</span>
            </div>
            <div class="map-marker-count" id="marker-count" style="display: none;">
                <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                <span><strong id="marker-count-value">0</strong> titik ditampilkan</span>
            </div>
        </div>
    </section>
    
    <!-- Info Grid -->
    <div class="map-info-grid">
        <!-- Legend Card -->
        <div class="map-info-card">
            <div class="map-info-card-header">
                <div class="map-info-card-icon" style="background: rgba(245, 158, 11, 0.2); color: var(--map-warning);">
                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                </div>
                <div class="map-info-card-title">Legenda & Panduan</div>
            </div>
            <div class="map-info-card-body">
                <div class="legend-item">
                    <div class="legend-dot cluster-small">1+</div>
                    <span class="legend-text">Cluster Kecil (1-10 massa)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot cluster-medium">10+</div>
                    <span class="legend-text">Cluster Sedang (10-50 massa)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot cluster-large">50+</div>
                    <span class="legend-text">Cluster Besar (50+ massa)</span>
                </div>
                <hr style="border: none; border-top: 1px solid var(--map-border); margin: 16px 0;">
                <p style="font-size: 11px; color: var(--map-muted); line-height: 1.5;">
                    <strong>Interaksi:</strong> Klik marker untuk detail, scroll untuk zoom,
                    drag untuk pan. Gunakan filter di atas untuk mempersempit pencarian.
                </p>
            </div>
        </div>
        
        <!-- Regional Distribution (Tabbed) -->
        <div class="map-info-card">
            <div class="map-info-card-header" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="map-info-card-icon" style="background: rgba(59, 130, 246, 0.2); color: var(--map-info);">
                        <i data-lucide="map" style="width: 14px; height: 14px;"></i>
                    </div>
                    <div class="map-info-card-title">Sebaran Wilayah</div>
                </div>
                <!-- Tabs -->
                <div class="tabs" style="display: flex; gap: 8px;">
                    <button onclick="switchTab('district')" id="tab-district" class="map-btn map-btn-primary" style="padding: 4px 10px; font-size: 11px;">Kecamatan</button>
                    <button onclick="switchTab('village')" id="tab-village" class="map-btn map-btn-secondary" style="padding: 4px 10px; font-size: 11px;">Kelurahan</button>
                </div>
            </div>
            <div class="map-info-card-body">
                <!-- District Stats -->
                <div id="content-district">
                    @forelse($districtStats as $stat)
                        <div class="province-stat">
                            <div class="province-stat-header">
                                <span class="province-stat-name">{{ $stat->district?->name ?? 'Unknown' }}</span>
                                <span class="province-stat-value">{{ number_format($stat->total) }}</span>
                            </div>
                            <div class="province-stat-bar">
                                @php
                                    $maxTotal = $districtStats->first()->total ?? 1;
                                    $percentage = ($stat->total / $maxTotal) * 100;
                                @endphp
                                <div class="province-stat-fill" style="width: {{ $percentage }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 20px; color: var(--map-muted); font-size: 12px;">
                            Belum ada data kecamatan
                        </div>
                    @endforelse
                </div>

                <!-- Village Stats -->
                <div id="content-village" style="display: none;">
                    @forelse($villageStats as $stat)
                        <div class="province-stat">
                            <div class="province-stat-header">
                                <span class="province-stat-name">{{ $stat->village?->name ?? 'Unknown' }}</span>
                                <span class="province-stat-value" style="color: var(--map-warning);">{{ number_format($stat->total) }}</span>
                            </div>
                            <div class="province-stat-bar">
                                @php
                                    $maxTotal = $villageStats->first()->total ?? 1;
                                    $percentage = ($stat->total / $maxTotal) * 100;
                                @endphp
                                <div class="province-stat-fill" style="width: {{ $percentage }}%; background: linear-gradient(90deg, var(--map-warning), #d97706);"></div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 20px; color: var(--map-muted); font-size: 12px;">
                            Belum ada data kelurahan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
(function() {
    'use strict';
    
    // ===== STATE =====
    let map = null;
    let markerCluster = null;
    let heatmapLayer = null;
    let isHeatmapVisible = false;
    let currentMarkers = [];
    
    // Yogyakarta center coordinates
    const YOGYAKARTA_CENTER = [-7.7956, 110.3695];
    const DEFAULT_ZOOM = 11;
    
    // ===== INITIALIZATION =====
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        setupEventListeners();
        
        // Auto-load markers for selected province
        setTimeout(() => {
            loadMarkers();
            loadRegencies();
        }, 500);
    });
    
    function initMap() {
        const container = document.getElementById('map-container');
        if (!container) return;
        
        // Create map instance
        // Create map instance restricted to DIY
        const diyBounds = [
            [-8.22, 110.00], // Southwest
            [-7.50, 110.85]  // Northeast
        ];

        map = L.map('map-container', {
            center: YOGYAKARTA_CENTER,
            zoom: DEFAULT_ZOOM,
            minZoom: 9, // Prevent zooming out too far
            maxBounds: diyBounds, // Restrict view to DIY
            maxBoundsViscosity: 1.0, // Hard limit
            zoomControl: true,
            scrollWheelZoom: true,
            keyboard: true,
            attributionControl: true
        });
        
        // Add tile layer with better styling
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
            minZoom: 5
        }).addTo(map);
        
        // Initialize marker cluster group
        markerCluster = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            maxClusterRadius: 60,
            disableClusteringAtZoom: 17,
            iconCreateFunction: createClusterIcon
        });
        
        map.addLayer(markerCluster);
        
        // Force resize after init
        setTimeout(() => map.invalidateSize(true), 300);
        
        // Refresh icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    function createClusterIcon(cluster) {
        const count = cluster.getChildCount();
        let sizeClass = 'cluster-small';
        let size = 36;
        
        if (count >= 50) {
            sizeClass = 'cluster-large';
            size = 52;
        } else if (count >= 10) {
            sizeClass = 'cluster-medium';
            size = 44;
        }
        
        return L.divIcon({
            html: `<div class="cluster-icon ${sizeClass}">${count}</div>`,
            className: 'marker-cluster-custom',
            iconSize: L.point(size, size)
        });
    }
    
    function setupEventListeners() {
        document.getElementById('filter-province').addEventListener('change', function() {
            loadRegencies();
            loadMarkers();
        });
        
        document.getElementById('filter-regency').addEventListener('change', function() {
            loadMarkers();
        });
        
        document.getElementById('map-layer').addEventListener('change', function() {
            if (this.value === 'heatmap') {
                showHeatmap();
            } else {
                hideHeatmap();
            }
        });
        
        // Keyboard accessibility for map
        document.getElementById('map-container').addEventListener('keydown', function(e) {
            const step = 50;
            switch(e.key) {
                case 'ArrowUp': map.panBy([0, -step]); break;
                case 'ArrowDown': map.panBy([0, step]); break;
                case 'ArrowLeft': map.panBy([-step, 0]); break;
                case 'ArrowRight': map.panBy([step, 0]); break;
                case '+': case '=': map.zoomIn(); break;
                case '-': map.zoomOut(); break;
            }
        });
    }
    
    // ===== DATA LOADING =====
    async function loadMarkers() {
        showLoading(true);
        
        const provinceId = document.getElementById('filter-province').value;
        const regencyId = document.getElementById('filter-regency').value;
        
        let url = '/maps/markers?';
        if (provinceId) url += `province=${provinceId}&`;
        if (regencyId) url += `regency=${regencyId}&`;
        url += `_t=${Date.now()}`; // Cache buster
        
        try {
            const response = await fetch(url, { 
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Check if response is OK (status 200-299)
            if (!response.ok) {
                console.error('Response not ok:', response.status, response.statusText);
                const text = await response.text();
                console.error('Response body:', text.substring(0, 200));
                showLoading(false);
                updateMarkerCount(0);
                return;
            }
            
            // Check content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Response is not JSON:', contentType);
                const text = await response.text();
                console.error('Response body:', text.substring(0, 200));
                showLoading(false);
                updateMarkerCount(0);
                return;
            }
            
            const data = await response.json();
            
            // Validate data is an array
            const markers = Array.isArray(data) ? data : (data.data || []);
            
            console.log('Loaded markers:', markers.length, 'items');
            
            markerCluster.clearLayers();
            currentMarkers = [];
            
            if (!markers || markers.length === 0) {
                showLoading(false);
                updateMarkerCount(0);
                return;
            }
            
            markers.forEach(m => {
                if (m.lat && m.lng) {
                    const marker = createMarker(m);
                    markerCluster.addLayer(marker);
                    currentMarkers.push(marker);
                }
            });
            
            // Update counter
            updateMarkerCount(currentMarkers.length);
            
            // Fit bounds
            if (currentMarkers.length > 0) {
                const bounds = markerCluster.getBounds();
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 13 });
            }
            
        } catch (error) {
            console.error('Error loading markers:', error);
        } finally {
            showLoading(false);
        }
    }
    
    function createMarker(data) {
        const icon = L.divIcon({
            html: `<div class="custom-marker-pin"></div>`,
            className: 'custom-marker-wrapper',
            iconSize: [24, 24],
            iconAnchor: [12, 12],
            popupAnchor: [0, -12]
        });
        
        const marker = L.marker([parseFloat(data.lat), parseFloat(data.lng)], { 
            icon: icon,
            title: data.name,
            alt: `Lokasi ${data.name}`
        });
        
        // Tooltip on hover
        marker.bindTooltip(data.name, {
            direction: 'top',
            offset: [0, -10],
            opacity: 0.95
        });
        
        // Popup on click
        const popupContent = `
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-avatar">${(data.name || 'U').charAt(0).toUpperCase()}</div>
                    <div>
                        <div class="popup-name">${escapeHtml(data.name || 'Unknown')}</div>
                        <div class="popup-id">ID: ${data.id}</div>
                    </div>
                </div>
                <div class="popup-detail">
                    <i data-lucide="map-pin" style="width: 14px; height: 14px;" class="popup-detail-icon"></i>
                    <span class="popup-detail-text">${escapeHtml(data.location || 'Lokasi tidak tersedia')}</span>
                </div>
                <div class="popup-coords">
                    📍 ${parseFloat(data.lat).toFixed(6)}, ${parseFloat(data.lng).toFixed(6)}
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent, {
            maxWidth: 300,
            className: 'custom-popup'
        });
        
        marker.on('popupopen', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        
        return marker;
    }
    
    async function loadRegencies() {
        const provinceId = document.getElementById('filter-province').value;
        const regencySelect = document.getElementById('filter-regency');
        
        regencySelect.innerHTML = '<option value="">Semua Kabupaten</option>';
        
        if (!provinceId) return;
        
        try {
            const response = await fetch(`/daftar/regencies?province_id=${provinceId}`, { 
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const regencies = await response.json();
            
            regencies.forEach(r => {
                const option = document.createElement('option');
                option.value = r.id;
                option.textContent = r.name;
                regencySelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading regencies:', error);
        }
    }
    
    // ===== HEATMAP =====
    async function showHeatmap() {
        if (heatmapLayer) {
            map.addLayer(heatmapLayer);
            isHeatmapVisible = true;
            markerCluster.clearLayers();
            return;
        }
        
        showLoading(true);
        
        try {
            const response = await fetch('/maps/heatmap', { 
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const points = await response.json();
            
            if (points.length === 0) {
                showLoading(false);
                return;
            }
            
            heatmapLayer = L.heatLayer(points, {
                radius: 25,
                blur: 15,
                maxZoom: 12,
                gradient: {
                    0.2: '#fee2e2',
                    0.4: '#fca5a5',
                    0.6: '#f87171',
                    0.8: '#dc2626',
                    1.0: '#7f1d1d'
                }
            });
            
            map.addLayer(heatmapLayer);
            isHeatmapVisible = true;
            markerCluster.clearLayers();
            
        } catch (error) {
            console.error('Error loading heatmap:', error);
        } finally {
            showLoading(false);
        }
    }
    
    function hideHeatmap() {
        if (heatmapLayer) {
            map.removeLayer(heatmapLayer);
        }
        isHeatmapVisible = false;
        loadMarkers();
    }
    
    // ===== UI HELPERS =====
    function showLoading(show) {
        const loader = document.getElementById('map-loading');
        if (loader) {
            loader.classList.toggle('hidden', !show);
        }
    }
    
    function updateMarkerCount(count) {
        const counter = document.getElementById('marker-count');
        const value = document.getElementById('marker-count-value');
        const statVisible = document.getElementById('stat-visible');
        
        if (counter && value) {
            value.textContent = count.toLocaleString();
            counter.style.display = count > 0 ? 'flex' : 'none';
        }
        
        if (statVisible) {
            statVisible.textContent = count.toLocaleString();
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ===== GLOBAL FUNCTIONS =====
    window.toggleHeatmap = function() {
        const select = document.getElementById('map-layer');
        if (isHeatmapVisible) {
            select.value = 'markers';
            hideHeatmap();
        } else {
            select.value = 'heatmap';
            showHeatmap();
        }
    };
    
    window.refreshMapData = function() {
        if (heatmapLayer) {
            map.removeLayer(heatmapLayer);
            heatmapLayer = null;
        }
        
        if (isHeatmapVisible) {
            showHeatmap();
        } else {
            loadMarkers();
        }
        
        map.invalidateSize(true);
    };
    window.switchTab = function(type) {
        if (type === 'district') {
            document.getElementById('content-district').style.display = 'block';
            document.getElementById('content-village').style.display = 'none';
            document.getElementById('tab-district').classList.replace('map-btn-secondary', 'map-btn-primary');
            document.getElementById('tab-village').classList.replace('map-btn-primary', 'map-btn-secondary');
        } else {
            document.getElementById('content-district').style.display = 'none';
            document.getElementById('content-village').style.display = 'block';
            document.getElementById('tab-district').classList.replace('map-btn-primary', 'map-btn-secondary');
            document.getElementById('tab-village').classList.replace('map-btn-secondary', 'map-btn-primary');
        }
    };
})();
</script>
@endpush

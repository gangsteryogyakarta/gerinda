@extends('layouts.app')

@section('title', 'Peta Sebaran Massa')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar" style="background: linear-gradient(135deg, #10B981, #059669);">
                <i data-lucide="map" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div>
                <h1>Peta Sebaran Massa</h1>
                <p>Visualisasi distribusi massa berdasarkan lokasi geografis</p>
            </div>
        </div>
        <div class="page-header-right">
            <button class="btn btn-secondary" onclick="toggleHeatmap()">
                <i data-lucide="flame"></i>
                Toggle Heatmap
            </button>
            <button class="btn btn-primary" onclick="refreshMap()">
                <i data-lucide="refresh-cw"></i>
                Refresh Data
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon primary">
                    <i data-lucide="users"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_massa']) }}</div>
            <div class="stat-label">Total Massa</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon success">
                    <i data-lucide="map-pin"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['geocoded']) }}</div>
            <div class="stat-label">Data Geocoded</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon warning">
                    <i data-lucide="globe"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['provinces_covered'] }}</div>
            <div class="stat-label">Provinsi Tercakup</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon info">
                    <i data-lucide="calendar"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_events'] }}</div>
            <div class="stat-label">Total Event</div>
        </div>
    </div>

    <!-- Map and Stats -->
    <div class="grid-3-1">
        <!-- Map Container -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--success-light); color: var(--success);">
                        <i data-lucide="map" style="width: 16px; height: 16px;"></i>
                    </div>
                    Peta Interaktif
                </div>
                <div style="display: flex; gap: 8px;">
                    <select id="filter-province" class="form-input" style="width: auto; padding: 8px 12px; font-size: 12px;">
                        <option value="">Semua Provinsi</option>
                        @php
                            $provinces = \App\Models\Province::orderBy('name')->get();
                        @endphp
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                    <select id="map-layer" class="form-input" style="width: auto; padding: 8px 12px; font-size: 12px;">
                        <option value="markers">Marker Cluster</option>
                        <option value="heatmap">Heatmap</option>
                    </select>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div id="map" class="map-container" style="height: 500px;"></div>
            </div>
        </div>

        <!-- Stats Sidebar -->
        <div>
            <!-- Province Distribution -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <div class="card-title">
                        <div class="card-title-icon">
                            <i data-lucide="bar-chart-3" style="width: 16px; height: 16px;"></i>
                        </div>
                        Top Provinsi
                    </div>
                </div>
                <div class="card-body" style="padding: 12px 20px;">
                    @foreach($provinceStats as $index => $stat)
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span style="font-size: 13px; font-weight: 500;">{{ $stat->province?->name ?? 'Unknown' }}</span>
                                <span style="font-size: 13px; font-weight: 700; color: var(--primary);">{{ number_format($stat->total) }}</span>
                            </div>
                            <div class="progress">
                                @php
                                    $maxTotal = $provinceStats->first()->total ?? 1;
                                    $percentage = ($stat->total / $maxTotal) * 100;
                                @endphp
                                <div class="progress-bar primary" style="width: {{ $percentage }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                    
                    @if($provinceStats->isEmpty())
                        <div style="text-align: center; padding: 40px 0; color: var(--text-muted);">
                            Belum ada data
                        </div>
                    @endif
                </div>
            </div>

            <!-- Legend -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <div class="card-title-icon" style="background: var(--warning-light); color: var(--warning);">
                            <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                        </div>
                        Legenda
                    </div>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 24px; height: 24px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">1+</div>
                            <span style="font-size: 13px;">Cluster Kecil (1-10)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 24px; height: 24px; background: #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">10+</div>
                            <span style="font-size: 13px;">Cluster Sedang (10-50)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 24px; height: 24px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">50+</div>
                            <span style="font-size: 13px;">Cluster Besar (50+)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 24px; height: 24px; background: linear-gradient(135deg, #3B82F6, #8B5CF6); border-radius: 4px;"></div>
                            <span style="font-size: 13px;">Lokasi Event</span>
                        </div>
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
    let map;
    let markerCluster;
    let heatmapLayer;
    let isHeatmapVisible = false;

    // Initialize map
    document.addEventListener('DOMContentLoaded', function() {
        // Create map centered on DI Yogyakarta
        map = L.map('map').setView([-7.7956, 110.3695], 11);

        // Add tile layer (CartoDB Light for better contrast with red markers)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Initialize marker cluster with custom styling
        markerCluster = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            maxClusterRadius: 50,
            iconCreateFunction: function(cluster) {
                const count = cluster.getChildCount();
                let size = 'small';
                let bgColor = '#DC2626';
                
                if (count >= 50) {
                    size = 'large';
                    bgColor = '#10B981';
                } else if (count >= 10) {
                    size = 'medium';
                    bgColor = '#F59E0B';
                }

                return L.divIcon({
                    html: `<div style="background: ${bgColor}; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">${count}</div>`,
                    className: 'marker-cluster',
                    iconSize: L.point(40, 40)
                });
            }
        });

        map.addLayer(markerCluster);

        // Load initial markers
        loadMarkers();

        // Event listeners
        document.getElementById('filter-province').addEventListener('change', loadMarkers);
        document.getElementById('map-layer').addEventListener('change', function() {
            if (this.value === 'heatmap') {
                showHeatmap();
            } else {
                hideHeatmap();
            }
        });

        // Refresh icons
        lucide.createIcons();
    });

    async function loadMarkers() {
        const provinceId = document.getElementById('filter-province').value;
        let url = '/maps/markers';
        if (provinceId) {
            url += `?province=${provinceId}`;
        }

        try {
            const response = await fetch(url);
            const markers = await response.json();

            markerCluster.clearLayers();

            const customIcon = L.divIcon({
                html: `<div style="background: var(--primary); width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>`,
                className: 'custom-marker',
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            markers.forEach(m => {
                const marker = L.marker([m.lat, m.lng], { icon: customIcon })
                    .bindPopup(`
                        <div style="min-width: 180px;">
                            <strong style="font-size: 14px;">${m.name}</strong><br>
                            <span style="color: #666; font-size: 12px;">${m.location}</span>
                        </div>
                    `);
                markerCluster.addLayer(marker);
            });

            // Fit bounds if markers exist
            if (markers.length > 0) {
                const bounds = L.latLngBounds(markers.map(m => [m.lat, m.lng]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }

        } catch (error) {
            console.error('Error loading markers:', error);
        }
    }

    async function showHeatmap() {
        if (heatmapLayer) {
            map.addLayer(heatmapLayer);
            isHeatmapVisible = true;
            markerCluster.clearLayers();
            return;
        }

        try {
            const response = await fetch('/maps/heatmap');
            const points = await response.json();

            heatmapLayer = L.heatLayer(points, {
                radius: 25,
                blur: 15,
                maxZoom: 10,
                gradient: {
                    0.4: '#FEE2E2',
                    0.65: '#F87171',
                    0.85: '#DC2626',
                    1.0: '#991B1B'
                }
            });

            map.addLayer(heatmapLayer);
            isHeatmapVisible = true;
            markerCluster.clearLayers();

        } catch (error) {
            console.error('Error loading heatmap:', error);
        }
    }

    function hideHeatmap() {
        if (heatmapLayer) {
            map.removeLayer(heatmapLayer);
        }
        isHeatmapVisible = false;
        loadMarkers();
    }

    function toggleHeatmap() {
        const select = document.getElementById('map-layer');
        if (isHeatmapVisible) {
            select.value = 'markers';
            hideHeatmap();
        } else {
            select.value = 'heatmap';
            showHeatmap();
        }
    }

    function refreshMap() {
        if (isHeatmapVisible) {
            if (heatmapLayer) {
                map.removeLayer(heatmapLayer);
                heatmapLayer = null;
            }
            showHeatmap();
        } else {
            loadMarkers();
        }
    }
</script>

<style>
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .leaflet-popup-content {
        margin: 12px 16px;
    }

    .leaflet-container {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
</style>
@endpush

{{-- resources/views/dashboard/ports.blade.php --}}
@extends('layouts.app')

@section('title', 'Port Location Dashboard')

@push('styles')
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>

    <style>
        /* ============================================
           ROOT VARIABLES — Tema Navy-Ink
           ============================================ */
        :root {
            --navy-bg: #0A111F;
            --navy-card: #131D31;
            --navy-surface: #1A2744;
            --navy-border: #2A3A5C;

            --signal-green: #3FA772;
            --signal-amber: #E3A038;
            --signal-red: #D6483F;
            --signal-cyan: #4FD1C5;

            --text-primary: #E8EDF5;
            --text-secondary: #94A9C9;
            --text-muted: #5A7A9E;

            --font-display: 'Space Grotesk', sans-serif;
            --font-body: 'Inter', sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
        }

        /* ============================================
           BASE LAYOUT
           ============================================ */
        body {
            background-color: var(--navy-bg);
            color: var(--text-primary);
            font-family: var(--font-body);
        }

        .page-wrapper {
            padding: 1.5rem 2rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ============================================
           SPLIT-FLAP HERO
           ============================================ */
        .splitflap-hero {
            background: var(--navy-card);
            border: 1px solid var(--navy-border);
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        }

        .splitflap-hero .hero-label {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .splitflap-hero .hero-label .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--signal-green);
            animation: pulse-dot 1.8s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.7); }
        }

        /* Board container — scroll horizontal otomatis */
        .splitflap-board {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.25rem 0.5rem;
            flex: 1 1 auto;
            min-width: 0;
            scrollbar-width: thin;
            scrollbar-color: var(--navy-border) transparent;
        }
        .splitflap-board::-webkit-scrollbar {
            height: 4px;
        }
        .splitflap-board::-webkit-scrollbar-thumb {
            background: var(--navy-border);
            border-radius: 4px;
        }

        /* Satu kartu flip */
        .flap-card {
            background: var(--navy-bg);
            border: 1px solid var(--navy-border);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            min-width: 110px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: border-color 0.2s, transform 0.2s;
        }
        .flap-card:hover {
            border-color: var(--signal-cyan);
            transform: translateY(-2px);
        }

        .flap-card .flap-country {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
            letter-spacing: 0.02em;
        }

        .flap-card .flap-score {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.1rem;
        }

        .flap-card .flap-risk {
            font-family: var(--font-mono);
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.1rem 0.5rem;
            border-radius: 20px;
            margin-top: 0.2rem;
        }
        .flap-risk.risk-low  { background: var(--signal-green); color: #fff; }
        .flap-risk.risk-med  { background: var(--signal-amber); color: #1a1a1a; }
        .flap-risk.risk-high { background: var(--signal-red); color: #fff; }

        /* ============================================
           SEARCH BAR
           ============================================ */
        .search-section {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .search-section .search-input-wrap {
            flex: 1 1 320px;
            display: flex;
            background: var(--navy-card);
            border: 1px solid var(--navy-border);
            border-radius: 8px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .search-section .search-input-wrap:focus-within {
            border-color: var(--signal-cyan);
            box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.15);
        }

        .search-section .search-input-wrap .input-icon {
            display: flex;
            align-items: center;
            padding: 0 0.75rem;
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .search-section .search-input-wrap input {
            background: transparent;
            border: none;
            padding: 0.6rem 0.75rem 0.6rem 0;
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 0.95rem;
            outline: none;
            width: 100%;
        }
        .search-section .search-input-wrap input::placeholder {
            color: var(--text-muted);
        }

        .search-section .btn-cyan {
            background: var(--signal-cyan);
            color: var(--navy-bg);
            border: none;
            padding: 0.6rem 1.4rem;
            border-radius: 8px;
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 0.85rem;
            transition: opacity 0.2s, transform 0.1s;
            cursor: pointer;
        }
        .search-section .btn-cyan:hover {
            opacity: 0.85;
            transform: scale(0.97);
        }

        .search-section .btn-outline {
            background: transparent;
            border: 1px solid var(--navy-border);
            color: var(--text-secondary);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-family: var(--font-body);
            font-size: 0.85rem;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s;
        }
        .search-section .btn-outline:hover {
            border-color: var(--text-secondary);
            color: var(--text-primary);
        }

        .search-section .stats-badge {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-left: auto;
            padding: 0.3rem 0.9rem;
            background: var(--navy-card);
            border-radius: 20px;
            border: 1px solid var(--navy-border);
        }

        /* ============================================
           MAP & LIST — Grid
           ============================================ */
        .port-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 992px) {
            .port-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Map Card */
        .map-card {
            background: var(--navy-card);
            border: 1px solid var(--navy-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .map-card #portMap {
            height: 480px;
            width: 100%;
            background: var(--navy-bg);
        }

        /* Leaflet overrides for dark theme */
        .leaflet-control-zoom a {
            background: var(--navy-surface) !important;
            color: var(--text-primary) !important;
            border-color: var(--navy-border) !important;
        }
        .leaflet-control-zoom a:hover {
            background: var(--navy-border) !important;
        }
        .leaflet-popup-content-wrapper {
            background: var(--navy-card) !important;
            color: var(--text-primary) !important;
            border-radius: 8px !important;
            border: 1px solid var(--navy-border);
        }
        .leaflet-popup-tip {
            background: var(--navy-card) !important;
        }
        .leaflet-popup-content {
            font-family: var(--font-body);
            font-size: 0.85rem;
        }
        .leaflet-popup-content strong {
            font-family: var(--font-display);
            font-size: 1rem;
        }

        /* List Card */
        .list-card {
            background: var(--navy-card);
            border: 1px solid var(--navy-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            max-height: 520px;
        }

        .list-card .list-header {
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid var(--navy-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .list-card .list-header .list-title {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .list-card .list-header .list-count {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--text-secondary);
            background: var(--navy-bg);
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            border: 1px solid var(--navy-border);
        }

        .list-card .list-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 0.25rem 0;
            scrollbar-width: thin;
            scrollbar-color: var(--navy-border) transparent;
        }
        .list-card .list-body::-webkit-scrollbar {
            width: 4px;
        }
        .list-card .list-body::-webkit-scrollbar-thumb {
            background: var(--navy-border);
            border-radius: 4px;
        }

        /* Item port di list */
        .port-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 1.25rem;
            border-bottom: 1px solid rgba(42, 58, 92, 0.3);
            cursor: pointer;
            transition: background 0.15s;
            font-family: var(--font-body);
        }
        .port-list-item:hover {
            background: rgba(79, 209, 197, 0.06);
        }
        .port-list-item:last-child {
            border-bottom: none;
        }

        .port-list-item .port-name {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-primary);
        }
        .port-list-item .port-name small {
            font-weight: 400;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-left: 0.5rem;
        }

        .port-list-item .port-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.7rem;
            font-family: var(--font-mono);
            color: var(--text-secondary);
        }
        .port-list-item .port-meta .country-tag {
            background: var(--navy-surface);
            padding: 0.1rem 0.6rem;
            border-radius: 12px;
            border: 1px solid var(--navy-border);
        }

        /* Loading & Empty States */
        .state-loading,
        .state-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
            font-family: var(--font-body);
            gap: 0.75rem;
        }
        .state-loading .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid var(--navy-border);
            border-top-color: var(--signal-cyan);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .state-empty .empty-icon {
            font-size: 2.4rem;
            opacity: 0.4;
        }

        /* Pagination */
        .pagination-wrap {
            display: flex;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--navy-border);
            flex-shrink: 0;
        }

        .pagination-wrap .pagination {
            margin: 0;
            gap: 0.2rem;
        }
        .pagination-wrap .page-item .page-link {
            background: transparent;
            border: 1px solid var(--navy-border);
            color: var(--text-secondary);
            font-family: var(--font-mono);
            font-size: 0.75rem;
            padding: 0.25rem 0.7rem;
            border-radius: 4px;
            transition: all 0.15s;
        }
        .pagination-wrap .page-item .page-link:hover {
            background: var(--navy-surface);
            border-color: var(--signal-cyan);
            color: var(--text-primary);
        }
        .pagination-wrap .page-item.active .page-link {
            background: var(--signal-cyan);
            border-color: var(--signal-cyan);
            color: var(--navy-bg);
        }
        .pagination-wrap .page-item.disabled .page-link {
            opacity: 0.3;
            pointer-events: none;
        }

        /* ============================================
           RESPONSIVE TWEAKS
           ============================================ */
        @media (max-width: 600px) {
            .page-wrapper { padding: 1rem; }
            .splitflap-hero { flex-direction: column; align-items: stretch; }
            .splitflap-hero .hero-label { justify-content: center; }
            .splitflap-board { padding: 0.25rem 0; }
            .search-section .search-input-wrap { flex: 1 1 100%; }
            .search-section .stats-badge { margin-left: 0; width: 100%; text-align: center; }
            .map-card #portMap { height: 280px; }
            .list-card { max-height: 400px; }
            .port-list-item { flex-wrap: wrap; gap: 0.3rem; }
            .port-list-item .port-meta { flex-wrap: wrap; }
        }
    </style>
@endpush

@section('content')
<div class="page-wrapper">

    {{-- ===== SPLIT-FLAP HERO ===== --}}
    <div class="splitflap-hero">
        <div class="hero-label">
            <span class="pulse-dot"></span>
            <span>HIGHEST RISK PORTS</span>
            <span style="font-weight:400;font-size:0.65rem;color:var(--text-muted);">● LIVE</span>
        </div>
        <div class="splitflap-board" id="splitflapBoard">
            {{-- Akan diisi oleh JavaScript --}}
            <div class="flap-card">
                <span class="flap-country">—</span>
                <span class="flap-score">loading...</span>
            </div>
        </div>
    </div>

    {{-- ===== SEARCH ===== --}}
    <div class="search-section">
        <div class="search-input-wrap">
            <span class="input-icon">⚓</span>
            <input type="text" id="searchInput" placeholder="Cari nama pelabuhan atau negara..." />
        </div>
        <button class="btn-cyan" id="searchBtn">Cari</button>
        <button class="btn-outline" id="resetBtn">↺ Reset</button>
        <span class="stats-badge" id="totalBadge">Total: 0</span>
    </div>

    {{-- ===== MAP + LIST ===== --}}
    <div class="port-grid">

        {{-- MAP --}}
        <div class="map-card">
            <div id="portMap"></div>
        </div>

        {{-- LIST --}}
        <div class="list-card">
            <div class="list-header">
                <span class="list-title">📋 Daftar Pelabuhan</span>
                <span class="list-count" id="listCount">0</span>
            </div>
            <div class="list-body" id="listBody">
                {{-- Loading state --}}
                <div class="state-loading" id="loadingState">
                    <div class="spinner"></div>
                    <span>Memuat data pelabuhan...</span>
                </div>
                {{-- List items --}}
                <div id="listItems" style="display:none;"></div>
                {{-- Empty state --}}
                <div class="state-empty" id="emptyState" style="display:none;">
                    <span class="empty-icon">🗺️</span>
                    <span>Tidak ada pelabuhan ditemukan</span>
                </div>
            </div>
            {{-- Pagination --}}
            <div class="pagination-wrap" id="paginationWrap" style="display:none;">
                <nav>
                    <ul class="pagination" id="paginationControls"></ul>
                </nav>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

    <script>
        (function() {
            'use strict';

            // ---- DOM refs ----
            const searchInput   = document.getElementById('searchInput');
            const searchBtn     = document.getElementById('searchBtn');
            const resetBtn      = document.getElementById('resetBtn');
            const totalBadge    = document.getElementById('totalBadge');
            const listCount     = document.getElementById('listCount');
            const listBody      = document.getElementById('listBody');
            const loadingState  = document.getElementById('loadingState');
            const listItems     = document.getElementById('listItems');
            const emptyState    = document.getElementById('emptyState');
            const paginationWrap= document.getElementById('paginationWrap');
            const paginationCtrl= document.getElementById('paginationControls');
            const splitflapBoard= document.getElementById('splitflapBoard');

            // ---- State ----
            let allPorts = [];
            let currentPage = 1;
            const perPage = 15;
            let currentSearch = '';

            // ---- Map ----
            let map = null;
            let markersLayer = null;

            function initMap() {
                if (map) {
                    map.invalidateSize();
                    return;
                }
                map = L.map('portMap', {
                    center: [20, 0],
                    zoom: 2,
                    zoomControl: true,
                    fadeAnimation: true,
                });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    className: 'leaflet-tile-dark',
                }).addTo(map);
                markersLayer = L.layerGroup().addTo(map);
            }

            // ---- Render Split-Flap ----
            function renderSplitflap(ports) {
                // Ambil 6 port dengan risiko tertinggi (sort by total_score descending)
                const sorted = [...ports]
                    .filter(p => p.country && p.country.risk_score)
                    .sort((a, b) => (b.country.risk_score?.total_score || 0) - (a.country.risk_score?.total_score || 0))
                    .slice(0, 6);

                if (sorted.length === 0) {
                    splitflapBoard.innerHTML = `
                        <div class="flap-card">
                            <span class="flap-country">—</span>
                            <span class="flap-score">no data</span>
                        </div>
                    `;
                    return;
                }

                let html = '';
                sorted.forEach(p => {
                    const score = p.country?.risk_score?.total_score ?? 0;
                    let level = 'low';
                    let label = 'LOW';
                    if (score >= 67) { level = 'high'; label = 'HIGH'; }
                    else if (score >= 34) { level = 'med'; label = 'MED'; }

                    html += `
                        <div class="flap-card">
                            <span class="flap-country">${escapeHtml(p.country?.name || '?')}</span>
                            <span class="flap-score">RISK ${score.toFixed(0)}</span>
                            <span class="flap-risk risk-${level}">${label}</span>
                        </div>
                    `;
                });
                splitflapBoard.innerHTML = html;
            }

            // ---- Fetch data ----
            function fetchPorts(search = '') {
                // Show loading
                loadingState.style.display = 'flex';
                listItems.style.display = 'none';
                emptyState.style.display = 'none';
                paginationWrap.style.display = 'none';

                const url = `/api/ports?search=${encodeURIComponent(search)}`;

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    let ports = [];
                    if (Array.isArray(data)) {
                        ports = data;
                    } else if (data.data && Array.isArray(data.data)) {
                        ports = data.data;
                    } else {
                        ports = [];
                    }

                    allPorts = ports;
                    currentSearch = search;
                    totalBadge.textContent = `Total: ${ports.length}`;

                    // Split-flap
                    renderSplitflap(ports);

                    // Map
                    updateMap(ports);

                    // List & pagination
                    if (ports.length === 0) {
                        loadingState.style.display = 'none';
                        emptyState.style.display = 'flex';
                        listCount.textContent = '0';
                        return;
                    }

                    currentPage = 1;
                    renderList(ports, currentPage);
                    renderPagination(ports.length);
                })
                .catch(err => {
                    loadingState.style.display = 'none';
                    emptyState.style.display = 'flex';
                    emptyState.innerHTML = `
                        <span class="empty-icon">⚠️</span>
                        <span>Gagal memuat data: ${err.message}</span>
                    `;
                    console.error('Fetch error:', err);
                });
            }

            // ---- Update Map ----
            function updateMap(ports) {
                if (!map) return;
                markersLayer.clearLayers();

                const bounds = L.latLngBounds();
                let hasValid = false;

                ports.forEach(p => {
                    const lat = parseFloat(p.latitude);
                    const lng = parseFloat(p.longitude);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const countryName = p.country?.name || 'Unknown';
                    const popup = `
                        <strong>${escapeHtml(p.name)}</strong><br>
                        <span style="color:var(--text-secondary);">${escapeHtml(countryName)}</span><br>
                        <span style="font-family:var(--font-mono);font-size:0.7rem;">UN/LOCODE: ${escapeHtml(p.unlocode || '-')}</span>
                    `;

                    const marker = L.marker([lat, lng])
                        .bindPopup(popup)
                        .addTo(markersLayer);

                    bounds.extend([lat, lng]);
                    hasValid = true;
                });

                if (hasValid) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                } else {
                    map.setView([20, 0], 2);
                }
            }

            // ---- Render List (client-side pagination) ----
            function renderList(ports, page) {
                const start = (page - 1) * perPage;
                const end = Math.min(start + perPage, ports.length);
                const pageItems = ports.slice(start, end);

                loadingState.style.display = 'none';
                listItems.style.display = 'block';
                emptyState.style.display = 'none';
                listCount.textContent = ports.length;

                if (pageItems.length === 0) {
                    listItems.innerHTML = `<div class="state-empty"><span>Tidak ada data di halaman ini</span></div>`;
                    return;
                }

                let html = '';
                pageItems.forEach(p => {
                    const countryName = p.country?.name || '-';
                    html += `
                        <div class="port-list-item" data-lat="${p.latitude}" data-lng="${p.longitude}" data-name="${escapeHtml(p.name)}">
                            <span class="port-name">
                                ${escapeHtml(p.name)}
                                <small>${escapeHtml(p.unlocode || '')}</small>
                            </span>
                            <span class="port-meta">
                                <span class="country-tag">${escapeHtml(countryName)}</span>
                                <span>${parseFloat(p.latitude).toFixed(2)}, ${parseFloat(p.longitude).toFixed(2)}</span>
                            </span>
                        </div>
                    `;
                });
                listItems.innerHTML = html;

                // Click -> fly to port
                document.querySelectorAll('.port-list-item').forEach(el => {
                    el.addEventListener('click', function() {
                        const lat = parseFloat(this.dataset.lat);
                        const lng = parseFloat(this.dataset.lng);
                        if (!isNaN(lat) && !isNaN(lng) && map) {
                            map.flyTo([lat, lng], 10, { duration: 1.2 });
                            // Cari marker & buka popup
                            markersLayer.eachLayer(function(layer) {
                                if (layer.getLatLng) {
                                    const pos = layer.getLatLng();
                                    if (Math.abs(pos.lat - lat) < 0.001 && Math.abs(pos.lng - lng) < 0.001) {
                                        layer.openPopup();
                                    }
                                }
                            });
                        }
                    });
                });
            }

            // ---- Pagination ----
            function renderPagination(total) {
                const totalPages = Math.ceil(total / perPage);
                if (totalPages <= 1) {
                    paginationWrap.style.display = 'none';
                    return;
                }
                paginationWrap.style.display = 'flex';

                let html = '';
                // Prev
                html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">‹</a>
                </li>`;

                let start = Math.max(1, currentPage - 2);
                let end = Math.min(totalPages, currentPage + 2);
                if (end - start < 4) {
                    if (start === 1) end = Math.min(totalPages, start + 4);
                    else start = Math.max(1, end - 4);
                }
                for (let i = start; i <= end; i++) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
                }

                html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">›</a>
                </li>`;

                paginationCtrl.innerHTML = html;

                paginationCtrl.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.dataset.page);
                        if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                            currentPage = page;
                            renderList(allPorts, currentPage);
                            renderPagination(allPorts.length);
                            listBody.scrollTop = 0;
                        }
                    });
                });
            }

            // ---- Utility ----
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // ---- Search handlers ----
            function doSearch() {
                const q = searchInput.value.trim();
                if (q !== currentSearch) {
                    currentPage = 1;
                    fetchPorts(q);
                } else {
                    fetchPorts(q); // refresh
                }
            }

            searchBtn.addEventListener('click', doSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
            });
            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                if (currentSearch !== '') {
                    currentPage = 1;
                    fetchPorts('');
                }
            });

            // ---- Init ----
            initMap();
            // Tunggu map ready
            setTimeout(() => {
                fetchPorts('');
            }, 300);
        })();
    </script>
@endpush
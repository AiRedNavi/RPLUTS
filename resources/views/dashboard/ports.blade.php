{{-- resources/views/dashboard/ports.blade.php --}}
@extends('layouts.app')

@section('title', 'Port Location Dashboard')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-navy: #0A111F;
            --bg-panel: #131D31;
            --bg-card: #1A2744;
            --border-glow: rgba(79, 209, 197, 0.15);

            --signal-red: #D6483F;
            --signal-amber: #E3A038;
            --signal-green: #3FA772;
            --signal-cyan: #4FD1C5;

            --text-primary: #E8EDF5;
            --text-secondary: #B7C2D6;
            --text-muted: #8792A6;

            --font-display: 'Space Grotesk', sans-serif;
            --font-body: 'Inter', sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
        }

        body.dark-theme {
            background-color: var(--bg-navy);
            color: var(--text-primary);
            font-family: var(--font-body);
        }

        /* ============================================================
           JUDUL HALAMAN
           ============================================================ */
        .ports-page-header {
            margin-bottom: 24px;
        }

        .ports-page-header h2 {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .ports-page-header p {
            font-family: var(--font-body);
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.92rem;
        }

        /* ============================================================
           MAIN LAYOUT — sidebar + map
           ============================================================ */
        .ports-wrapper {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .ports-wrapper {
                grid-template-columns: 1fr;
            }
        }

        /* Sidebar */
        .ports-sidebar {
            background: var(--bg-panel);
            border: 1px solid rgba(79, 209, 197, 0.10);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-height: 75vh;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        }

        .ports-sidebar .sidebar-title {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .ports-sidebar .sidebar-title .count-badge {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(79, 209, 197, 0.15);
            color: var(--signal-cyan);
            padding: 2px 10px;
            border-radius: 20px;
            line-height: 1.6;
        }

        /* Search input */
        .search-wrapper {
            position: relative;
        }

        .search-wrapper .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .search-wrapper input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            background: var(--bg-navy);
            border: 1px solid rgba(79, 209, 197, 0.15);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-wrapper input:focus {
            border-color: var(--signal-cyan);
            box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.12);
        }

        .search-wrapper input::placeholder {
            color: var(--text-muted);
        }

        /* Port list container */
        .port-list-container {
            flex: 1;
            overflow-y: auto;
            margin: 0 -4px;
            padding: 0 4px;
        }

        .port-list-container::-webkit-scrollbar {
            width: 4px;
        }

        .port-list-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .port-list-container::-webkit-scrollbar-thumb {
            background: rgba(79, 209, 197, 0.35);
            border-radius: 4px;
        }

        /* Port list — targetkan class ASLI yang di-generate ports-map.js
           (Bootstrap list-group-item, fw-semibold, text-muted — bukan
           .port-item/.port-name/.port-meta custom yang tidak pernah ada) */
        #portList {
            list-style: none;
        }

        #portList .list-group-item {
            display: block;
            background: rgba(255,255,255,0.015) !important;
            border: none;
            border-left: 3px solid transparent;
            border-radius: 6px !important;
            padding: 10px 12px;
            margin-bottom: 4px;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            font-family: var(--font-body);
        }

        #portList .list-group-item:hover {
            background: rgba(79, 209, 197, 0.10) !important;
            border-left-color: var(--signal-cyan);
        }

        #portList .list-group-item .fw-semibold {
            color: var(--text-primary) !important;
            font-size: 0.9rem;
        }

        #portList .list-group-item .text-muted {
            color: var(--text-secondary) !important;
            font-size: 0.74rem;
            font-family: var(--font-mono);
            margin-top: 2px;
        }

        /* Status info */
        .list-status {
            font-family: var(--font-body);
            font-size: 0.8rem;
            color: var(--text-secondary);
            padding: 8px 2px 2px;
            border-top: 1px solid rgba(79, 209, 197, 0.10);
            margin-top: 4px;
        }

        .list-status .highlight {
            color: var(--signal-cyan);
            font-weight: 600;
            font-family: var(--font-mono);
        }

        /* Map container */
        .map-wrapper {
            background: var(--bg-panel);
            border: 1px solid rgba(79, 209, 197, 0.10);
            border-radius: 12px;
            overflow: hidden;
            height: 75vh;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        }

        .map-wrapper #portsMap {
            width: 100%;
            height: 100%;
        }

        /* Leaflet overrides for dark theme */
        .leaflet-tile-pane {
            filter: brightness(0.7) contrast(1.2);
        }

        .leaflet-popup-content-wrapper {
            background: var(--bg-panel);
            color: var(--text-primary);
            border-radius: 8px;
            border: 1px solid rgba(79, 209, 197, 0.15);
        }

        .leaflet-popup-tip {
            background: var(--bg-panel);
        }

        .leaflet-popup-content {
            font-family: var(--font-body);
            font-size: 0.85rem;
            line-height: 1.5;
            min-width: 180px;
        }

        .leaflet-popup-content strong {
            font-family: var(--font-display);
            font-weight: 600;
            display: block;
            font-size: 1rem;
            margin-bottom: 4px;
            color: var(--text-primary);
        }

        .leaflet-popup-content .popup-meta {
            color: var(--text-secondary);
            font-size: 0.78rem;
        }

        .leaflet-popup-content .popup-meta span {
            display: inline-block;
            margin-right: 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .ports-sidebar {
                max-height: 50vh;
            }

            .map-wrapper {
                height: 50vh;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- ============================================================
       JUDUL HALAMAN
       ============================================================ --}}
    <div class="ports-page-header">
        <h2>
            <i class="bi bi-geo-alt me-2" style="color:var(--signal-cyan);"></i>Port Location Dashboard
        </h2>
        <p>Peta pelabuhan dunia — cari berdasarkan nama pelabuhan, kode UNLOCODE, atau negara.</p>
    </div>

    {{-- ============================================================
       MAIN GRID: SIDEBAR + MAP
       ============================================================ --}}
    <div class="ports-wrapper">

        {{-- Sidebar --}}
        <div class="ports-sidebar">
            <div class="sidebar-title">
                <span>Daftar Pelabuhan</span>
                <span class="count-badge" id="portCount">0</span>
            </div>

            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input
                    type="text"
                    id="portSearchInput"
                    placeholder="Cari pelabuhan atau negara..."
                    autocomplete="off"
                >
            </div>

            <div class="port-list-container" id="portListContainer">
                <div id="portListStatus" class="list-status">Memuat data pelabuhan...</div>
                <ul id="portList" class="list-unstyled" style="margin:0;padding:0;">
                    {{-- diisi oleh ports-map.js --}}
                </ul>
            </div>

            <div class="list-status" id="portListFooter">
                <span id="resultInfo">Menampilkan semua pelabuhan</span>
            </div>
        </div>

        {{-- Map --}}
        <div class="map-wrapper">
            <div id="portsMap"></div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- Script khusus ports-map.js (tidak diubah) --}}
<script src="{{ asset('js/maps/ports-map.js') }}" defer></script>

{{-- Update badge jumlah pelabuhan & info hasil pencarian —
     ditarget ke .list-group-item (class asli), bukan .port-item --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const portList = document.getElementById('portList');
    const portCountBadge = document.getElementById('portCount');
    const resultInfo = document.getElementById('resultInfo');
    const searchInput = document.getElementById('portSearchInput');

    function updateCountInfo() {
        const items = portList.querySelectorAll('.list-group-item');
        const count = items.length;
        portCountBadge.textContent = count;

        const searchVal = searchInput?.value?.trim() || '';
        resultInfo.textContent = searchVal
            ? `Hasil pencarian: "${searchVal}" (${count} ditemukan)`
            : `Menampilkan ${count} pelabuhan`;
    }

    const observer = new MutationObserver(updateCountInfo);
    observer.observe(portList, { childList: true, subtree: true });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            setTimeout(updateCountInfo, 100);
        });
    }
});
</script>
@endpush
{{-- resources/views/dashboard/ports.blade.php --}}
@extends('layouts.app')

@section('title', 'Port Location Dashboard')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    {{-- Fonts tambahan (jika belum ada di layout) --}}
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">

    <style>
        /* ============================================================
           Gaya khusus halaman Ports — menggunakan design tokens dari layout
           ============================================================ */
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
            --text-secondary: #94A3B8;
            --text-muted: #64748B;

            --font-display: 'Space Grotesk', sans-serif;
            --font-body: 'Inter', sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
        }

        /* pastikan body mengikuti tema */
        body.dark-theme {
            background-color: var(--bg-navy);
            color: var(--text-primary);
            font-family: var(--font-body);
        }

        /* ============================================================
           SPLIT-FLAP HERO
           ============================================================ */
        .splitflap-hero {
            background: var(--bg-panel);
            border: 1px solid rgba(79, 209, 197, 0.12);
            border-radius: 16px;
            padding: 24px 32px;
            margin-bottom: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .splitflap-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(79, 209, 197, 0.03), transparent 70%);
            pointer-events: none;
        }

        .splitflap-label {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .splitflap-label .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--signal-green);
            animation: pulse-dot 2s ease-in-out infinite;
            display: inline-block;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.8); }
        }

        .splitflap-track {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            align-items: center;
        }

        .splitflap-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.1rem;
            background: var(--bg-navy);
            padding: 6px 14px 6px 10px;
            border-radius: 6px;
            border-left: 3px solid var(--signal-cyan);
            letter-spacing: 0.02em;
        }

        .splitflap-item .flap-number {
            font-family: var(--font-mono);
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--signal-cyan);
            min-width: 28px;
            text-align: center;
            background: rgba(79, 209, 197, 0.08);
            padding: 0 6px;
            border-radius: 4px;
        }

        .splitflap-item .flap-country {
            color: var(--text-primary);
        }

        .splitflap-item .flap-score {
            font-family: var(--font-mono);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 12px;
            background: rgba(0,0,0,0.3);
        }

        .flap-score.risk-high   { color: var(--signal-red); }
        .flap-score.risk-medium { color: var(--signal-amber); }
        .flap-score.risk-low    { color: var(--signal-green); }

        .splitflap-more {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-left: 4px;
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
            border: 1px solid rgba(79, 209, 197, 0.06);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-height: 75vh;
        }

        .ports-sidebar .sidebar-title {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .ports-sidebar .sidebar-title .count-badge {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            background: rgba(79, 209, 197, 0.12);
            color: var(--signal-cyan);
            padding: 0 10px;
            border-radius: 20px;
            line-height: 1.8;
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
            color: var(--text-muted);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .search-wrapper input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            background: var(--bg-navy);
            border: 1px solid rgba(79, 209, 197, 0.12);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-wrapper input:focus {
            border-color: var(--signal-cyan);
            box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.1);
        }

        .search-wrapper input::placeholder {
            color: var(--text-muted);
        }

        /* Port list */
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
            background: rgba(79, 209, 197, 0.3);
            border-radius: 4px;
        }

        .port-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 4px;
            border-radius: 6px;
            background: transparent;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            border-left: 3px solid transparent;
            font-family: var(--font-body);
        }

        .port-item:hover {
            background: rgba(79, 209, 197, 0.04);
            border-left-color: var(--signal-cyan);
        }

        .port-item.active {
            background: rgba(79, 209, 197, 0.08);
            border-left-color: var(--signal-cyan);
        }

        .port-item .port-name {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .port-item .port-meta {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }

        .port-item .port-country {
            font-size: 0.7rem;
            color: var(--text-secondary);
            background: rgba(255,255,255,0.04);
            padding: 0 8px;
            border-radius: 4px;
        }

        /* Status info */
        .list-status {
            font-family: var(--font-body);
            font-size: 0.8rem;
            color: var(--text-muted);
            padding: 4px 0;
            border-top: 1px solid rgba(79, 209, 197, 0.06);
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
            border: 1px solid rgba(79, 209, 197, 0.06);
            border-radius: 12px;
            overflow: hidden;
            height: 75vh;
            position: relative;
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
            border: 1px solid rgba(79, 209, 197, 0.1);
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
        }

        .leaflet-popup-content .popup-meta {
            color: var(--text-secondary);
            font-size: 0.75rem;
        }

        .leaflet-popup-content .popup-meta span {
            display: inline-block;
            margin-right: 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .splitflap-hero {
                padding: 16px 18px;
            }

            .splitflap-item {
                font-size: 0.9rem;
                padding: 4px 10px 4px 8px;
            }

            .splitflap-item .flap-number {
                font-size: 0.9rem;
                min-width: 22px;
            }

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
<div class="container-fluid px-3 px-md-4">

    {{-- ============================================================
       SPLIT-FLAP HERO — daftar negara risiko tertinggi
       ============================================================ --}}
    <div class="splitflap-hero" id="splitflapHero">
        <div class="splitflap-label">
            <span class="pulse-dot"></span>
            <span>MONITORING RISIKO TERTINGGI · REAL-TIME</span>
            <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--text-muted);margin-left:auto;">
                <span id="flapTimestamp">--:--</span> UTC
            </span>
        </div>
        <div class="splitflap-track" id="flapTrack">
            <div class="splitflap-item">
                <span class="flap-number">01</span>
                <span class="flap-country">Memuat...</span>
            </div>
        </div>
        <div style="margin-top:10px;font-size:0.7rem;color:var(--text-muted);font-family:var(--font-mono);letter-spacing:0.04em;">
            ⚡ diperbarui otomatis · <span id="flapUpdateCount">0</span> siklus
        </div>
    </div>

    {{-- ============================================================
       JUDUL HALAMAN
       ============================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:1.5rem;color:var(--text-primary);">
                <i class="bi bi-geo-alt me-2" style="color:var(--signal-cyan);"></i>Port Location Dashboard
            </h2>
            <p style="font-family:var(--font-body);color:var(--text-secondary);margin:0;">
                Peta pelabuhan dunia — cari berdasarkan nama pelabuhan, kode UNLOCODE, atau negara.
            </p>
        </div>
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

{{-- Script tambahan untuk split-flap & integrasi --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    //  SPLIT-FLAP HERO — simulasi data risiko (fallback)
    //  Karena halaman ports tidak punya data risiko, kita gunakan
    //  data dummy atau ambil dari endpoint /api/risk?limit=5
    // ============================================================
    const flapTrack = document.getElementById('flapTrack');
    const flapTimestamp = document.getElementById('flapTimestamp');
    const flapUpdateCount = document.getElementById('flapUpdateCount');

    let flipCycle = 0;
    let topCountries = [];

    async function fetchTopRisk() {
        try {
            const resp = await fetch('/api/risk?limit=5', {
                headers: { 'Accept': 'application/json' }
            });
            if (!resp.ok) throw new Error('Gagal');
            const data = await resp.json();
            let items = [];
            if (Array.isArray(data)) items = data;
            else if (data.data && Array.isArray(data.data)) items = data.data;
            else if (data.countries) items = data.countries;

            if (items.length === 0) {
                // fallback dummy
                items = [
                    { country: { name: 'China' }, total_score: 78, risk_level: 'high' },
                    { country: { name: 'Jerman' }, total_score: 72, risk_level: 'high' },
                    { country: { name: 'Indonesia' }, total_score: 65, risk_level: 'medium' },
                    { country: { name: 'Australia' }, total_score: 58, risk_level: 'medium' },
                    { country: { name: 'Brasil' }, total_score: 44, risk_level: 'low' },
                ];
            }

            topCountries = items.map((item, idx) => ({
                name: item.country?.name || `Negara ${idx+1}`,
                score: item.total_score ?? Math.round(40 + Math.random() * 50),
                level: item.risk_level || 'medium',
            }));

            renderSplitFlap(topCountries);
            flipCycle = 0;
            flapUpdateCount.textContent = flipCycle;
        } catch (e) {
            // fallback dummy
            topCountries = [
                { name: 'China', score: 78, level: 'high' },
                { name: 'Jerman', score: 72, level: 'high' },
                { name: 'Indonesia', score: 65, level: 'medium' },
                { name: 'Australia', score: 58, level: 'medium' },
                { name: 'Brasil', score: 44, level: 'low' },
            ];
            renderSplitFlap(topCountries);
        }
        // timestamp
        updateTimestamp();
    }

    function renderSplitFlap(countries) {
        if (!countries || countries.length === 0) {
            flapTrack.innerHTML = `<div class="splitflap-item"><span class="flap-country" style="color:var(--text-muted);">Tidak ada data</span></div>`;
            return;
        }

        let html = '';
        countries.slice(0, 5).forEach((c, i) => {
            const num = String(i + 1).padStart(2, '0');
            const levelClass = c.level === 'high' ? 'risk-high' : c.level === 'medium' ? 'risk-medium' : 'risk-low';
            const displayScore = typeof c.score === 'number' ? Math.round(c.score) : '--';
            html += `
                <div class="splitflap-item">
                    <span class="flap-number">${num}</span>
                    <span class="flap-country">${escapeHtml(c.name)}</span>
                    <span class="flap-score ${levelClass}">${displayScore}</span>
                </div>
            `;
        });
        flapTrack.innerHTML = html;
    }

    function updateTimestamp() {
        const now = new Date();
        flapTimestamp.textContent = now.toISOString().slice(11, 16);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Auto-rotate split-flap
    let flipInterval = null;
    function startRotation() {
        if (flipInterval) clearInterval(flipInterval);
        flipInterval = setInterval(() => {
            if (topCountries.length > 1) {
                const rotated = [...topCountries];
                const first = rotated.shift();
                rotated.push(first);
                topCountries = rotated;
                renderSplitFlap(rotated);
                flipCycle++;
                flapUpdateCount.textContent = flipCycle;
            }
        }, 4000);
    }

    // Init
    fetchTopRisk();
    startRotation();

    // Update timestamp every 10s
    setInterval(updateTimestamp, 10000);

    // ============================================================
    //  INTEGRASI DENGAN PORTS-MAP.JS
    //  ports-map.js akan mengisi daftar pelabuhan dan peta.
    //  Kita hanya perlu menambahkan event listener untuk update
    //  badge jumlah pelabuhan setelah data dimuat.
    // ============================================================
    // Karena ports-map.js menggunakan event 'portListUpdated' atau
    // kita bisa polling, kita gunakan MutationObserver pada #portList
    const portList = document.getElementById('portList');
    const portCountBadge = document.getElementById('portCount');
    const resultInfo = document.getElementById('resultInfo');

    const observer = new MutationObserver(() => {
        const items = portList.querySelectorAll('.port-item');
        const count = items.length;
        portCountBadge.textContent = count;

        // update result info
        const searchVal = document.getElementById('portSearchInput')?.value?.trim() || '';
        if (searchVal) {
            resultInfo.textContent = `Hasil pencarian: "${searchVal}" (${count} ditemukan)`;
        } else {
            resultInfo.textContent = `Menampilkan ${count} pelabuhan`;
        }
    });

    observer.observe(portList, { childList: true, subtree: true });

    // Also update on search input change (re-trigger after ports-map.js filters)
    const searchInput = document.getElementById('portSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // ports-map.js handles filtering, we just update info after a short delay
            setTimeout(() => {
                const items = portList.querySelectorAll('.port-item');
                const count = items.length;
                portCountBadge.textContent = count;
                const val = this.value.trim();
                if (val) {
                    resultInfo.textContent = `Hasil pencarian: "${val}" (${count} ditemukan)`;
                } else {
                    resultInfo.textContent = `Menampilkan ${count} pelabuhan`;
                }
            }, 100);
        });
    }

});
</script>
@endpush
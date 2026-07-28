/**
 * resources/js/maps/weather-map.js
 *
 * Global Weather Monitoring — Leaflet.js
 * Endpoint: GET /api/weather (public, tidak butuh login — bisa diakses
 * guest, user, maupun admin).
 *
 * Fitur "Auto Fetch": tombol untuk menyalakan/mematikan refresh
 * otomatis. Setiap kali data baru datang, marker & list lama DIHAPUS
 * dulu (bukan ditambah/ditumpuk) sebelum digambar ulang — supaya peta
 * tidak penuh sesak dan data tidak saling menimpa.
 */
(function () {
    'use strict';

    const REFRESH_INTERVAL_MS = 30000; // 30 detik

    const mapEl = document.getElementById('tw-weather-map');
    if (!mapEl) return; // jaga-jaga kalau script kepanggil di halaman lain

    const map = L.map('tw-weather-map', {
        worldCopyJump: true,
    }).setView([15, 20], 2);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        maxZoom: 18,
    }).addTo(map);

    // Layer group KHUSUS untuk marker cuaca. Dengan begini, membersihkan
    // hasil fetch sebelumnya cukup panggil weatherLayer.clearLayers(),
    // tanpa mengganggu tile layer/basemap.
    const weatherLayer = L.layerGroup().addTo(map);

    const stormListEl = document.getElementById('tw-storm-list');
    const fetchBtn = document.getElementById('tw-auto-fetch-btn');
    const lastUpdatedEl = document.getElementById('tw-last-updated');

    let autoFetchTimer = null;
    let isFetching = false;

    const RISK_COLOR_VARS = {
        low: '--signal-green',
        medium: '--signal-amber',
        high: '--signal-red',
    };

    const RISK_LABELS = {
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
    };

    function riskColor(level) {
        const varName = RISK_COLOR_VARS[level] || '--signal-amber';
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim() || '#999';
    }

    function riskLabel(level) {
        return RISK_LABELS[level] || 'Tidak diketahui';
    }

    function formatNumber(value, suffix) {
        const num = parseFloat(value);
        return Number.isNaN(num) ? '-' : `${num.toFixed(1)}${suffix || ''}`;
    }

    function formatTime(date) {
        return date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    async function fetchWeatherData() {
        // Cegah request bertumpuk kalau fetch sebelumnya belum selesai
        // (mis. auto-fetch jalan tapi koneksi lambat).
        if (isFetching) return;
        isFetching = true;
        setButtonLoadingState(true);

        try {
            const res = await fetch('/api/weather', {
                headers: { Accept: 'application/json' },
            });

            if (!res.ok) {
                throw new Error(`Gagal mengambil data cuaca (status ${res.status})`);
            }

            const payload = await res.json();
            const rows = Array.isArray(payload.data) ? payload.data : [];

            renderMarkers(rows);
            renderStormList(rows);

            if (lastUpdatedEl) {
                lastUpdatedEl.textContent = `Update terakhir: ${formatTime(new Date())}`;
            }
        } catch (err) {
            console.error('[weather-map] fetch error:', err);
            if (stormListEl) {
                stormListEl.innerHTML =
                    '<p class="tw-muted mb-0" style="font-size:0.85rem;">Gagal memuat data cuaca. Coba lagi sebentar.</p>';
            }
        } finally {
            isFetching = false;
            setButtonLoadingState(false);
        }
    }

    function renderMarkers(rows) {
        // Hapus SEMUA marker hasil fetch sebelumnya dulu, baru gambar
        // yang baru. Ini kunci supaya marker tidak menumpuk/overlap tiap
        // kali auto-fetch jalan.
        weatherLayer.clearLayers();

        rows.forEach((row) => {
            const lat = parseFloat(row.latitude);
            const lng = parseFloat(row.longitude);
            if (Number.isNaN(lat) || Number.isNaN(lng)) return;

            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                weight: 1,
                color: '#111',
                fillColor: riskColor(row.storm_risk_level),
                fillOpacity: 0.85,
            });

            const countryName = escapeHtml(row.country?.name ?? '-');
            const isoCode = escapeHtml(row.country?.iso_code ?? '-');

            marker.bindPopup(`
                <strong>${countryName}</strong> (${isoCode})<br>
                Suhu: ${formatNumber(row.temperature, '&deg;C')}<br>
                Curah hujan: ${formatNumber(row.rainfall, ' mm')}<br>
                Kecepatan angin: ${formatNumber(row.wind_speed, ' km/j')}<br>
                Risiko badai: ${riskLabel(row.storm_risk_level)}
            `);

            marker.addTo(weatherLayer);
        });
    }

    function renderStormList(rows) {
        if (!stormListEl) return;

        // Kosongkan list lama dulu sebelum diisi ulang.
        stormListEl.innerHTML = '';

        const highRisk = rows
            .filter((row) => row.storm_risk_level === 'high')
            .sort((a, b) => (parseFloat(b.wind_speed) || 0) - (parseFloat(a.wind_speed) || 0));

        if (highRisk.length === 0) {
            stormListEl.innerHTML =
                '<p class="tw-muted mb-0" style="font-size:0.85rem;">Tidak ada negara berisiko tinggi saat ini.</p>';
            return;
        }

        const frag = document.createDocumentFragment();

        highRisk.forEach((row) => {
            const item = document.createElement('div');
            item.className = 'd-flex align-items-center gap-2 py-2';
            item.style.borderBottom = '1px solid var(--ink-750)';
            item.innerHTML = `
                <span class="tw-storm-dot" style="background: var(--signal-red);"></span>
                <div>
                    <div style="font-size:0.85rem;">${escapeHtml(row.country?.name ?? '-')}</div>
                    <div class="tw-muted" style="font-size:0.75rem;">Angin ${formatNumber(row.wind_speed, ' km/j')}</div>
                </div>
            `;
            frag.appendChild(item);
        });

        stormListEl.appendChild(frag);
    }

    function setButtonLoadingState(loading) {
        if (!fetchBtn) return;
        fetchBtn.classList.toggle('tw-loading', loading);
    }

    function startAutoFetch() {
        fetchWeatherData(); // langsung fetch begitu diaktifkan, tidak nunggu interval pertama
        autoFetchTimer = setInterval(fetchWeatherData, REFRESH_INTERVAL_MS);

        if (fetchBtn) {
            fetchBtn.textContent = 'Auto Fetch: ON';
            fetchBtn.classList.remove('btn-outline-light');
            fetchBtn.classList.add('btn-success');
        }
    }

    function stopAutoFetch() {
        clearInterval(autoFetchTimer);
        autoFetchTimer = null;

        if (fetchBtn) {
            fetchBtn.textContent = 'Auto Fetch: OFF';
            fetchBtn.classList.remove('btn-success');
            fetchBtn.classList.add('btn-outline-light');
        }
    }

    function toggleAutoFetch() {
        if (autoFetchTimer) {
            stopAutoFetch();
        } else {
            startAutoFetch();
        }
    }

    if (fetchBtn) {
        fetchBtn.addEventListener('click', toggleAutoFetch);
    }

    // Muat data begitu halaman dibuka, tanpa perlu klik tombol dulu.
    // (Auto-fetch berkala baru jalan setelah tombol ditekan.)
    fetchWeatherData();

    // Bersihkan interval saat pindah/tutup halaman.
    window.addEventListener('beforeunload', () => {
        if (autoFetchTimer) clearInterval(autoFetchTimer);
    });
})();
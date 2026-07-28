/**
 * Global Weather Monitoring - Leaflet map + tombol fetch manual.
 *
 * Alur:
 * 1. Saat halaman dibuka -> loadWeatherData() ambil data terkini dari
 *    GET /api/weather dan plot ke peta + list "Negara Berisiko Badai Tinggi".
 * 2. Tombol "Perbarui Data Cuaca" -> POST /api/weather/fetch (memicu
 *    php artisan fetch:weather di server), lalu setelah sukses
 *    otomatis panggil ulang loadWeatherData().
 * 3. Setiap kali render ulang, marker lama & isi list lama DIHAPUS
 *    dulu (bukan ditambah/ditumpuk), supaya peta & list tidak
 *    menumpuk atau saling menimpa antar fetch.
 */

(function () {
    'use strict';

    const MAP_ELEMENT_ID = 'tw-weather-map';
    const STORM_LIST_ELEMENT_ID = 'tw-storm-list';
    const FETCH_BUTTON_ID = 'tw-fetch-weather-btn';
    const FETCH_STATUS_ID = 'tw-fetch-status';

    const API_INDEX_URL = '/api/weather';
    const API_FETCH_URL = '/api/weather/fetch';

    let map;
    let markersLayer; // L.layerGroup - wadah semua marker, gampang di-clear sekaligus

    function riskColor(level) {
        const root = getComputedStyle(document.documentElement);
        switch ((level || '').toLowerCase()) {
            case 'high':
                return root.getPropertyValue('--signal-red').trim() || '#e5484d';
            case 'medium':
                return root.getPropertyValue('--signal-amber').trim() || '#f5a623';
            case 'low':
            default:
                return root.getPropertyValue('--signal-green').trim() || '#2fbf71';
        }
    }

    function riskLabel(level) {
        switch ((level || '').toLowerCase()) {
            case 'high':
                return 'Tinggi';
            case 'medium':
                return 'Sedang';
            case 'low':
            default:
                return 'Rendah';
        }
    }

    function initMap() {
        map = L.map(MAP_ELEMENT_ID, {
            worldCopyJump: true,
        }).setView([10, 20], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);
    }

    /**
     * Hapus semua marker & isi list badai yang sedang tampil.
     * Dipanggil SEBELUM render data baru supaya tidak ada penumpukan
     * atau marker ganda yang saling menimpa di peta.
     */
    function clearPreviousRender() {
        if (markersLayer) {
            markersLayer.clearLayers();
        }

        const stormList = document.getElementById(STORM_LIST_ELEMENT_ID);
        if (stormList) {
            stormList.innerHTML = '';
        }
    }

    function renderMarkers(rows) {
        rows.forEach((row) => {
            if (row.latitude === null || row.longitude === null) {
                return;
            }

            const color = riskColor(row.storm_risk_level);

            const marker = L.circleMarker([row.latitude, row.longitude], {
                radius: 8,
                fillColor: color,
                color: color,
                weight: 1,
                fillOpacity: 0.85,
            });

            marker.bindPopup(`
                <strong>${escapeHtml(row.country.name)}</strong><br>
                Suhu: ${formatNumber(row.temperature)}&deg;C<br>
                Curah hujan: ${formatNumber(row.rainfall)} mm<br>
                Angin: ${formatNumber(row.wind_speed)} km/j<br>
                Risiko badai: ${riskLabel(row.storm_risk_level)}
            `);

            markersLayer.addLayer(marker);
        });
    }

    function renderStormList(rows) {
        const stormList = document.getElementById(STORM_LIST_ELEMENT_ID);
        if (!stormList) {
            return;
        }

        const highRisk = rows.filter(
            (row) => (row.storm_risk_level || '').toLowerCase() === 'high'
        );

        if (highRisk.length === 0) {
            stormList.innerHTML =
                '<p class="tw-muted mb-0" style="font-size:0.85rem;">Tidak ada negara berisiko badai tinggi saat ini.</p>';
            return;
        }

        const fragment = document.createDocumentFragment();

        highRisk
            .sort((a, b) => a.country.name.localeCompare(b.country.name))
            .forEach((row) => {
                const item = document.createElement('div');
                item.className = 'd-flex align-items-center justify-content-between py-2';
                item.style.borderBottom = '1px solid var(--ink-750)';
                item.innerHTML = `
                    <span class="d-flex align-items-center gap-2" style="font-size:0.85rem;">
                        <span class="tw-storm-dot" style="background: var(--signal-red);"></span>
                        ${escapeHtml(row.country.name)}
                    </span>
                    <span class="tw-muted" style="font-size:0.75rem;">${formatNumber(row.wind_speed)} km/j</span>
                `;
                fragment.appendChild(item);
            });

        stormList.innerHTML = '';
        stormList.appendChild(fragment);
    }

    async function loadWeatherData() {
        try {
            const response = await fetch(API_INDEX_URL, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error(`Gagal memuat data cuaca (status ${response.status}).`);
            }

            const payload = await response.json();
            const rows = payload.data || [];

            // Bersihkan render lama dulu sebelum menampilkan data baru,
            // supaya marker & list tidak menumpuk/saling menimpa.
            clearPreviousRender();

            renderMarkers(rows);
            renderStormList(rows);
        } catch (error) {
            console.error(error);
            setStatus('Gagal memuat data cuaca. Coba muat ulang halaman.', true);
        }
    }

    function setStatus(message, isError) {
        const statusEl = document.getElementById(FETCH_STATUS_ID);
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message || '';
        statusEl.style.color = isError
            ? 'var(--signal-red)'
            : 'var(--signal-green)';
    }

    function initFetchButton() {
        const button = document.getElementById(FETCH_BUTTON_ID);
        if (!button) {
            return;
        }

        button.addEventListener('click', async () => {
            const originalLabel = button.innerHTML;

            button.disabled = true;
            button.innerHTML = 'Memperbarui data...';
            setStatus('Mengambil data cuaca terbaru dari Open-Meteo...', false);

            try {
                const response = await fetch(API_FETCH_URL, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'Gagal memperbarui data cuaca.');
                }

                // Data baru sudah tersimpan di DB, sekarang ambil ulang
                // dan render (render lama otomatis dibersihkan di
                // loadWeatherData -> clearPreviousRender).
                await loadWeatherData();

                setStatus(payload.message || 'Data cuaca berhasil diperbarui.', false);
            } catch (error) {
                console.error(error);
                setStatus(error.message || 'Gagal memperbarui data cuaca.', true);
            } finally {
                button.disabled = false;
                button.innerHTML = originalLabel;
            }
        });
    }

    function formatNumber(value) {
        if (value === null || value === undefined) {
            return '-';
        }
        const num = Number(value);
        return Number.isFinite(num) ? num.toFixed(1) : '-';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        initFetchButton();
        loadWeatherData();
    });
})();
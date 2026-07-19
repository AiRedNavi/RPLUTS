/**
 * weather-map.js — Global Weather Monitoring
 * Inisialisasi Leaflet.js, plot marker cuaca tiap negara dari /api/weather,
 * warna marker mengikuti storm_risk_level (hijau/amber/merah).
 */

const API_BASE = '/api';

function stormColor(level) {
    return { low: '#3FA772', medium: '#E3A038', high: '#D6483F' }[level] || '#8FA0BD';
}

function stormBadgeClass(level) {
    return { low: 'tw-badge--low', medium: 'tw-badge--medium', high: 'tw-badge--high' }[level] || 'tw-badge--neutral';
}

let map;

function initMap() {
    map = L.map('tw-weather-map', {
        worldCopyJump: true,
    }).setView([10, 20], 2);

    // Tile layer gelap (CartoDB dark matter) supaya nyambung dengan tema
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        maxZoom: 18,
    }).addTo(map);
}

async function loadWeatherMarkers() {
    try {
        const res = await fetch(`${API_BASE}/weather`);
        const json = await res.json();
        const items = json.data || [];

        const stormListEl = document.getElementById('tw-storm-list');
        const highRiskCountries = [];

        items.forEach((item) => {
            if (!item.latitude || !item.longitude) return;

            const color = stormColor(item.storm_risk_level);

            const marker = L.circleMarker([item.latitude, item.longitude], {
                radius: 7,
                fillColor: color,
                color: color,
                weight: 1,
                fillOpacity: 0.75,
            }).addTo(map);

            marker.bindPopup(`
                <strong>${item.country.name}</strong> (${item.country.iso_code})<br>
                Suhu: ${item.temperature ?? '—'}°C<br>
                Curah hujan: ${item.rainfall ?? '—'} mm<br>
                Angin: ${item.wind_speed ?? '—'} km/h<br>
                Risiko badai: <strong style="color:${color}">${(item.storm_risk_level || '—').toUpperCase()}</strong>
            `);

            if (item.storm_risk_level === 'high') {
                highRiskCountries.push(item);
            }
        });

        // Panel daftar negara risiko badai tinggi di sisi kanan
        if (highRiskCountries.length === 0) {
            stormListEl.innerHTML = '<p class="tw-muted mb-0" style="font-size:0.85rem;">Tidak ada negara dengan risiko badai tinggi saat ini.</p>';
        } else {
            stormListEl.innerHTML = highRiskCountries.map((item) => `
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom tw-divider">
                    <div>
                        <div class="fw-medium" style="font-size:0.88rem;">${item.country.name}</div>
                        <div class="tw-muted font-mono" style="font-size:0.75rem;">${item.temperature ?? '—'}°C · ${item.wind_speed ?? '—'} km/h</div>
                    </div>
                    <span class="tw-badge ${stormBadgeClass(item.storm_risk_level)}">HIGH</span>
                </div>
            `).join('');
        }
    } catch (err) {
        console.error('loadWeatherMarkers error:', err);
        document.getElementById('tw-storm-list').innerHTML = '<p class="tw-muted mb-0" style="font-size:0.85rem;">Gagal memuat data cuaca.</p>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initMap();
    loadWeatherMarkers();
});
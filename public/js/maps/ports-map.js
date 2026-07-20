/**
 * resources/js/maps/ports-map.js
 * Port Location Dashboard — peta Leaflet + search bar (Fase 5).
 * Fetch data dari GET /api/ports (dengan opsi ?search=).
 */

(function () {
    const API_URL = '/api/ports';
    const mapEl = document.getElementById('portsMap');
    if (!mapEl) return; // guard: script cuma jalan di halaman ports

    const searchInput = document.getElementById('portSearchInput');
    const listEl = document.getElementById('portList');
    const statusEl = document.getElementById('portListStatus');

    // Init peta, default center di sekitar Asia Tenggara, zoom dunia
    const map = L.map('portsMap').setView([5, 100], 3);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const portIcon = L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
    });

    let markersLayer = L.layerGroup().addTo(map);
    let debounceTimer = null;

    function renderMarkersAndList(ports) {
        markersLayer.clearLayers();
        listEl.innerHTML = '';

        if (ports.length === 0) {
            statusEl.textContent = 'Tidak ada pelabuhan yang cocok.';
            return;
        }

        statusEl.textContent = `${ports.length} pelabuhan ditemukan.`;

        const bounds = [];

        ports.forEach((port) => {
            const lat = port.latitude;
            const lng = port.longitude;

            if (lat && lng) {
                const marker = L.marker([lat, lng], { icon: portIcon }).addTo(markersLayer);
                marker.bindPopup(
                    `<strong>${escapeHtml(port.name)}</strong><br>` +
                    `${escapeHtml(port.country?.name ?? '-')}<br>` +
                    `UNLOCODE: ${escapeHtml(port.unlocode ?? '-')}`
                );
                bounds.push([lat, lng]);

                marker._portListItem = attachListItem(port, marker);
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 6 });
        }
    }

    function attachListItem(port, marker) {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action';
        li.style.cursor = 'pointer';
        li.innerHTML = `
            <div class="fw-semibold">${escapeHtml(port.name)}</div>
            <div class="text-muted small">${escapeHtml(port.country?.name ?? '-')} · ${escapeHtml(port.unlocode ?? '-')}</div>
        `;
        li.addEventListener('click', () => {
            map.flyTo(marker.getLatLng(), 8, { duration: 0.8 });
            marker.openPopup();
        });
        listEl.appendChild(li);
        return li;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    async function fetchPorts(search = '') {
        statusEl.textContent = 'Memuat data pelabuhan...';
        try {
            const url = search ? `${API_URL}?search=${encodeURIComponent(search)}` : API_URL;
            const res = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const json = await res.json();
            const ports = json.data ?? json; // support Resource collection wrapper

            renderMarkersAndList(ports);
        } catch (err) {
            console.error('Gagal memuat data pelabuhan:', err);
            statusEl.textContent = 'Gagal memuat data pelabuhan. Coba muat ulang halaman.';
        }
    }

    // Search bar dengan debounce 400ms supaya tidak spam request tiap ketikan
    searchInput?.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const value = e.target.value.trim();
        debounceTimer = setTimeout(() => fetchPorts(value), 400);
    });

    // Load awal
    fetchPorts();
})();
const API_BASE = '/api';
const watchlistCharts = {}; // simpan instance chart per country_id, supaya bisa di-destroy saat reload

function riskBadgeClass(level) {
    return { low: 'tw-badge--low', medium: 'tw-badge--medium', high: 'tw-badge--high' }[level] || 'tw-badge--neutral';
}

async function loadWatchlist() {
    const grid = document.getElementById('tw-watchlist-grid');
    const empty = document.getElementById('tw-watchlist-empty');

    try {
        const res = await fetch(`${API_BASE}/watchlist`, { headers: { 'Accept': 'application/json' } });

        if (res.status === 401) {
            window.location.href = '/login';
            return;
        }

        const json = await res.json();
        const items = json.data || [];

        // Hapus semua chart instance lama sebelum render ulang
        Object.values(watchlistCharts).forEach((chart) => chart.destroy());
        for (const key in watchlistCharts) delete watchlistCharts[key];

        if (items.length === 0) {
            empty.classList.remove('d-none');
            grid.innerHTML = '';
            return;
        }

        empty.classList.add('d-none');

        grid.innerHTML = items.map((item) => {
            const risk = item.risk_score;
            const riskBadge = risk
                ? `<span class="tw-badge ${riskBadgeClass(risk.risk_level)}">${risk.risk_level.toUpperCase()} · ${parseFloat(risk.total_score).toFixed(0)}</span>`
                : '<span class="tw-badge tw-badge--neutral">BELUM DIHITUNG</span>';

            const isBasisCurrency = item.currency_code === 'USD';

            return `
                <div class="col-md-6 col-xl-4">
                    <div class="tw-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="tw-eyebrow">${item.iso_code}</div>
                                <h5 class="mb-0">${item.name}</h5>
                                <div class="tw-muted" style="font-size:0.8rem;">${item.region || '—'} · ${item.currency_code || '—'}</div>
                            </div>
                            ${riskBadge}
                        </div>

                        <hr class="tw-divider">

                        <div class="tw-eyebrow mb-1" style="font-size:0.68rem;">
                            Tren Kurs — USD → ${item.currency_code || '—'}
                        </div>

                        ${isBasisCurrency
                            ? `<div class="tw-watchlist-basis-note">Ini adalah mata uang basis (USD), tidak ada tren perbandingan.</div>`
                            : `<div class="tw-watchlist-chart-wrap"><canvas id="tw-wl-chart-${item.country_id}"></canvas></div>`
                        }

                        <button class="btn btn-sm btn-outline-danger w-100 mt-3" data-country-id="${item.country_id}">
                            Hapus dari Watchlist
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        // Setelah DOM ter-render, baru load chart tiap negara (butuh canvas sudah ada)
        items.forEach((item) => {
            if (item.currency_code && item.currency_code !== 'USD') {
                loadCurrencyChart(item.country_id, item.currency_code);
            }
        });

        grid.querySelectorAll('[data-country-id]').forEach((btn) => {
            btn.addEventListener('click', () => removeFromWatchlist(btn.dataset.countryId));
        });
    } catch (err) {
        grid.innerHTML = '<p class="tw-muted">Gagal memuat watchlist.</p>';
        console.error('loadWatchlist error:', err);
    }
}

async function loadCurrencyChart(countryId, targetCode) {
    const canvas = document.getElementById(`tw-wl-chart-${countryId}`);
    if (!canvas) return;

    try {
        const res = await fetch(`${API_BASE}/currency/history?base=USD&target=${targetCode}`);
        const json = await res.json();
        const history = json.data || [];

        const labels = history.map((h) => h.recorded_date);
        const values = history.map((h) => parseFloat(h.rate));

        const chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['—'],
                datasets: [{
                    data: values.length ? values : [0],
                    borderColor: '#4FD1C5',
                    backgroundColor: 'rgba(79,209,197,0.1)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 0,
                    borderWidth: 1.5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: {
                        display: true,
                        ticks: { color: '#8FA0BD', font: { family: 'IBM Plex Mono', size: 9 }, maxTicksLimit: 3 },
                        grid: { color: '#1E2C46' },
                    },
                },
            },
        });

        watchlistCharts[countryId] = chart;
    } catch (err) {
        console.error(`loadCurrencyChart error for ${targetCode}:`, err);
    }
}

async function removeFromWatchlist(countryId) {
    if (!confirm('Hapus negara ini dari watchlist?')) return;

    try {
        const res = await fetch(`${API_BASE}/watchlist/${countryId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        });

        if (res.ok) {
            loadWatchlist();
        } else {
            alert('Gagal menghapus dari watchlist.');
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan.');
        console.error('removeFromWatchlist error:', err);
    }
}

document.addEventListener('DOMContentLoaded', loadWatchlist);
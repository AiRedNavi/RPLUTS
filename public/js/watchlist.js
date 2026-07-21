const API_BASE = '/api';

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

        if (items.length === 0) {
            empty.classList.remove('d-none');
            grid.innerHTML = '';
            return;
        }

        empty.classList.add('d-none');

        grid.innerHTML = items.map((item) => {
            const risk = item.risk_score;
            const badge = risk
                ? `<span class="tw-badge ${riskBadgeClass(risk.risk_level)}">${risk.risk_level.toUpperCase()} · ${parseFloat(risk.total_score).toFixed(0)}</span>`
                : '<span class="tw-badge tw-badge--neutral">BELUM DIHITUNG</span>';

            return `
                <div class="col-md-4">
                    <div class="tw-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="tw-eyebrow">${item.iso_code}</div>
                                <h5 class="mb-0">${item.name}</h5>
                            </div>
                            ${badge}
                        </div>
                        <div class="tw-muted mb-3" style="font-size:0.85rem;">
                            ${item.region || '—'} · ${item.currency ? item.currency.code : '—'}
                        </div>
                        <button class="btn btn-sm btn-outline-danger" data-country-id="${item.country_id}">
                            Hapus dari Watchlist
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        grid.querySelectorAll('[data-country-id]').forEach((btn) => {
            btn.addEventListener('click', () => removeFromWatchlist(btn.dataset.countryId));
        });
    } catch (err) {
        grid.innerHTML = '<p class="tw-muted">Gagal memuat watchlist.</p>';
        console.error('loadWatchlist error:', err);
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
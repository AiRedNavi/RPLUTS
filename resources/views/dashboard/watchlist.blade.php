@extends('layouts.app')

@section('title', 'Watchlist')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="tw-eyebrow">Favorite Monitoring</div>
    <h2 class="font-display mb-4">Watchlist Kamu</h2>

    <div id="tw-watchlist-empty" class="tw-card text-center py-5 d-none">
        <div class="tw-eyebrow mb-2">Watchlist masih kosong</div>
        <h3 class="mb-2">Belum ada negara yang dipantau</h3>
        <p class="tw-muted mb-3">Cari negara di dashboard utama, lalu tekan "Tambah ke Watchlist" pada detail negara.</p>
        <a href="{{ url('/') }}" class="btn btn-sm" style="background: var(--signal-green); color: var(--ink-950); font-weight:600;">Ke Dashboard</a>
    </div>

    <div id="tw-watchlist-grid" class="row g-3"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const grid = document.getElementById('tw-watchlist-grid');
    const emptyState = document.getElementById('tw-watchlist-empty');

    function riskBadgeClass(level) {
        if (level === 'high') return 'tw-badge--high';
        if (level === 'medium') return 'tw-badge--medium';
        if (level === 'low') return 'tw-badge--low';
        return '';
    }

    function riskCardClass(level) {
        if (level === 'high') return 'tw-card--risk-high';
        if (level === 'medium') return 'tw-card--risk-medium';
        if (level === 'low') return 'tw-card--risk-low';
        return '';
    }

    async function loadWatchlist() {
        try {
            const res = await fetch('/api/watchlist', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (res.status === 401) {
                window.location.href = '/login';
                return;
            }

            const json = await res.json();
            const items = json.data || [];

            grid.innerHTML = '';

            if (items.length === 0) {
                emptyState.classList.remove('d-none');
                return;
            }
            emptyState.classList.add('d-none');

            items.forEach(item => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';
                col.innerHTML = `
                    <div class="tw-card ${riskCardClass(item.risk_level)} h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="tw-eyebrow mb-1">${item.iso_code ?? '—'}</div>
                                <h5 class="font-display mb-0">${item.country_name}</h5>
                            </div>
                            <span class="tw-badge ${riskBadgeClass(item.risk_level)}">${item.risk_level ?? 'N/A'}</span>
                        </div>
                        <div class="font-mono mb-3" style="font-size:1.4rem;">
                            ${item.risk_score !== null && item.risk_score !== undefined ? item.risk_score : '—'}
                            <span class="tw-muted" style="font-size:0.85rem;">/ 100</span>
                        </div>
                        <button class="btn btn-sm btn-outline-danger tw-btn-remove" data-country-id="${item.country_id}">
                            Hapus dari Watchlist
                        </button>
                    </div>
                `;
                grid.appendChild(col);
            });

            grid.querySelectorAll('.tw-btn-remove').forEach(btn => {
                btn.addEventListener('click', () => removeFromWatchlist(btn.dataset.countryId));
            });
        } catch (err) {
            grid.innerHTML = '<p class="tw-muted">Gagal memuat watchlist. Coba refresh halaman.</p>';
        }
    }

    async function removeFromWatchlist(countryId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        try {
            await fetch(`/api/watchlist/${countryId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
            });
            loadWatchlist();
        } catch (err) {
            alert('Gagal menghapus dari watchlist.');
        }
    }

    loadWatchlist();
});
</script>
@endpush
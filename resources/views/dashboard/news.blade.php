{{-- resources/views/dashboard/news.blade.php --}}
@extends('layouts.app')

@section('title', 'Berita')

@push('styles')
{{-- Fonts --}}
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       ROOT & WARNA DASAR
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

    body.dark-theme {
        background-color: var(--bg-navy);
        color: var(--text-primary);
        font-family: var(--font-body);
    }

    /* ============================================================
       FILTER BAR
       ============================================================ */
    .filter-bar {
        background: var(--bg-panel);
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 28px;
        border: 1px solid rgba(79, 209, 197, 0.06);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px 20px;
    }

    .filter-bar .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-bar label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        font-family: var(--font-display);
    }

    .filter-bar select,
    .filter-bar input {
        background: var(--bg-navy);
        border: 1px solid rgba(79, 209, 197, 0.15);
        border-radius: 8px;
        color: var(--text-primary);
        font-family: var(--font-body);
        font-size: 0.9rem;
        padding: 6px 14px;
        outline: none;
        transition: border-color 0.2s;
        min-width: 140px;
    }

    .filter-bar select:focus,
    .filter-bar input:focus {
        border-color: var(--signal-cyan);
        box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.1);
    }

    .filter-bar select option {
        background: var(--bg-panel);
    }

    .btn-cyan {
        background: var(--signal-cyan);
        color: var(--bg-navy);
        border: none;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 6px 20px;
        border-radius: 8px;
        transition: all 0.2s;
        font-family: var(--font-display);
    }

    .btn-cyan:hover {
        background: #3dbdb0;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(79, 209, 197, 0.25);
        color: var(--bg-navy);
    }

    .btn-outline-cyan {
        background: transparent;
        border: 1px solid rgba(79, 209, 197, 0.3);
        color: var(--signal-cyan);
        font-weight: 500;
        font-size: 0.85rem;
        padding: 6px 18px;
        border-radius: 8px;
        transition: all 0.2s;
        font-family: var(--font-body);
    }

    .btn-outline-cyan:hover {
        background: rgba(79, 209, 197, 0.08);
        border-color: var(--signal-cyan);
    }

    /* ============================================================
       SENTIMENT BADGE
       ============================================================ */
    .badge-sentiment {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 0.7rem;
        padding: 4px 14px;
        border-radius: 20px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .badge-sentiment.positive {
        background: rgba(63, 167, 114, 0.18);
        color: var(--signal-green);
        border: 1px solid rgba(63, 167, 114, 0.25);
    }

    .badge-sentiment.neutral {
        background: rgba(227, 160, 56, 0.18);
        color: var(--signal-amber);
        border: 1px solid rgba(227, 160, 56, 0.25);
    }

    .badge-sentiment.negative {
        background: rgba(214, 72, 63, 0.18);
        color: var(--signal-red);
        border: 1px solid rgba(214, 72, 63, 0.25);
    }

    .badge-sentiment.unknown {
        background: rgba(148, 163, 184, 0.12);
        color: var(--text-secondary);
        border: 1px solid rgba(148, 163, 184, 0.15);
    }

    /* ============================================================
       NEWS CARD
       ============================================================ */
    .news-card {
        background: var(--bg-panel);
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 14px;
        border: 1px solid rgba(79, 209, 197, 0.05);
        transition: all 0.2s;
        cursor: default;
        position: relative;
    }

    .news-card:hover {
        border-color: rgba(79, 209, 197, 0.15);
        background: #172340;
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    .news-card .news-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 8px;
    }

    .news-card .news-title {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--text-primary);
        flex: 1;
        min-width: 200px;
        line-height: 1.4;
    }

    .news-card .news-title a {
        color: var(--text-primary);
        text-decoration: none;
        transition: color 0.2s;
    }

    .news-card .news-title a:hover {
        color: var(--signal-cyan);
    }

    .news-card .news-summary {
        font-family: var(--font-body);
        font-size: 0.92rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .news-card .news-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 18px;
        font-size: 0.78rem;
        color: var(--text-muted);
        font-family: var(--font-body);
    }

    .news-card .news-meta .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .news-card .news-meta .meta-item .label {
        color: var(--text-muted);
        font-weight: 400;
    }

    .news-card .news-meta .meta-item .value {
        color: var(--text-secondary);
        font-weight: 500;
    }

    .news-card .news-meta .country-tag {
        background: rgba(79, 209, 197, 0.08);
        color: var(--signal-cyan);
        padding: 2px 12px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        font-family: var(--font-display);
        letter-spacing: 0.04em;
    }

    /* ============================================================
       STATS ROW (sentimen counters)
       ============================================================ */
    .sentiment-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 20px;
        align-items: center;
        padding: 0 4px;
    }

    .sentiment-stats .stat-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        font-family: var(--font-mono);
    }

    .sentiment-stats .stat-item .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .sentiment-stats .stat-item .dot.green { background: var(--signal-green); }
    .sentiment-stats .stat-item .dot.amber { background: var(--signal-amber); }
    .sentiment-stats .stat-item .dot.red   { background: var(--signal-red); }

    .sentiment-stats .stat-item .count {
        font-weight: 600;
        color: var(--text-primary);
        min-width: 20px;
        text-align: center;
    }

    /* ============================================================
       PAGINATION
       ============================================================ */
    .pagination-cyan .page-link {
        background: var(--bg-panel);
        border: 1px solid rgba(79, 209, 197, 0.12);
        color: var(--text-secondary);
        font-family: var(--font-mono);
        font-size: 0.8rem;
        padding: 6px 14px;
        transition: all 0.2s;
    }

    .pagination-cyan .page-link:hover {
        background: rgba(79, 209, 197, 0.08);
        border-color: var(--signal-cyan);
        color: var(--signal-cyan);
    }

    .pagination-cyan .page-item.active .page-link {
        background: var(--signal-cyan);
        border-color: var(--signal-cyan);
        color: var(--bg-navy);
        font-weight: 600;
    }

    .pagination-cyan .page-item.disabled .page-link {
        opacity: 0.3;
        cursor: not-allowed;
    }

    /* ============================================================
       LOADING & EMPTY STATES
       ============================================================ */
    .loading-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        gap: 16px;
    }

    .loading-spinner .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(79, 209, 197, 0.1);
        border-top-color: var(--signal-cyan);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-spinner p {
        color: var(--text-muted);
        font-family: var(--font-body);
        font-size: 0.9rem;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
        font-family: var(--font-body);
    }

    .empty-state .empty-icon {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .empty-state h5 {
        font-family: var(--font-display);
        color: var(--text-secondary);
        margin-bottom: 8px;
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 768px) {
        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-bar .filter-group {
            flex-wrap: wrap;
        }

        .filter-bar select,
        .filter-bar input {
            min-width: 100%;
        }

        .news-card .news-header {
            flex-direction: column;
        }

        .news-card .news-title {
            font-size: 0.95rem;
        }

        .sentiment-stats {
            gap: 6px 14px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- ============================================================
    FILTER BAR
    ============================================================ --}}
    <div class="filter-bar">
        <div class="filter-group">
            <label for="categoryFilter">Kategori</label>
            <select id="categoryFilter">
                <option value="">Semua</option>
                <option value="logistics">Logistik</option>
                <option value="trade">Perdagangan</option>
                <option value="shipping">Pengiriman</option>
                <option value="economy">Ekonomi</option>
                <option value="geopolitics">Geopolitik</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="countryFilter">Negara</label>
            <select id="countryFilter">
                <option value="">Semua</option>
                {{-- akan diisi JS --}}
            </select>
        </div>

        <div class="filter-group" style="flex:1;min-width:160px;">
            <label for="searchInput">Cari</label>
            <input type="text" id="searchInput" placeholder="Kata kunci..." style="min-width:120px;flex:1;">
        </div>

        <div style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;">
            <button class="btn-cyan" id="applyFilterBtn">
                <span style="display:flex;align-items:center;gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Cari
                </span>
            </button>
            <button class="btn-outline-cyan" id="resetFilterBtn">Reset</button>
        </div>
    </div>

    {{-- ============================================================
    SENTIMENT STATS + TOTAL
    ============================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div class="sentiment-stats" id="sentimentStats">
            <span class="stat-item">
                <span class="dot green"></span>
                <span class="count" id="countPositive">0</span>
                <span style="color:var(--text-muted);font-family:var(--font-body);font-size:0.75rem;">positif</span>
            </span>
            <span class="stat-item">
                <span class="dot amber"></span>
                <span class="count" id="countNeutral">0</span>
                <span style="color:var(--text-muted);font-family:var(--font-body);font-size:0.75rem;">netral</span>
            </span>
            <span class="stat-item">
                <span class="dot red"></span>
                <span class="count" id="countNegative">0</span>
                <span style="color:var(--text-muted);font-family:var(--font-body);font-size:0.75rem;">negatif</span>
            </span>
        </div>
        <div style="font-family:var(--font-mono);font-size:0.8rem;color:var(--text-muted);">
            total <span id="totalNewsCount" style="color:var(--signal-cyan);font-weight:600;">0</span> berita
        </div>
    </div>

    {{-- ============================================================
    NEWS LIST
    ============================================================ --}}
    <div id="newsListContainer">
        {{-- loading --}}
        <div class="loading-spinner" id="loadingIndicator">
            <div class="spinner"></div>
            <p>Memuat berita terkini...</p>
        </div>

        {{-- error --}}
        <div id="errorMessage" style="display:none;background:rgba(214,72,63,0.08);border:1px solid rgba(214,72,63,0.2);border-radius:12px;padding:20px;color:var(--signal-red);font-family:var(--font-body);text-align:center;">
            <strong>Gagal memuat berita</strong>
            <p id="errorText" style="margin-top:6px;font-size:0.9rem;color:var(--text-secondary);"></p>
        </div>

        {{-- items --}}
        <div id="newsItems" style="display:none;"></div>

        {{-- empty --}}
        <div class="empty-state" id="emptyState" style="display:none;">
            <div class="empty-icon">📭</div>
            <h5>Tidak ada berita</h5>
            <p>Coba ubah filter atau refresh halaman.</p>
        </div>
    </div>

    {{-- ============================================================
    PAGINATION
    ============================================================ --}}
    <nav id="paginationNav" style="display:none;margin-top:24px;">
        <ul class="pagination justify-content-center pagination-cyan" id="paginationControls">
            {{-- diisi JS --}}
        </ul>
    </nav>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    //  STATE
    // ============================================================
    const state = {
        currentPage: 1,
        perPage: 10,
        category: '',
        country: '',
        search: '',
        totalItems: 0,
        lastPage: 1,
        allCountries: [],
    };

    // ============================================================
    //  DOM REFS
    // ============================================================
    const $ = id => document.getElementById(id);
    const els = {
        loading: $('loadingIndicator'),
        error: $('errorMessage'),
        errorText: $('errorText'),
        items: $('newsItems'),
        empty: $('emptyState'),
        paginationNav: $('paginationNav'),
        paginationControls: $('paginationControls'),

        category: $('categoryFilter'),
        country: $('countryFilter'),
        search: $('searchInput'),
        applyBtn: $('applyFilterBtn'),
        resetBtn: $('resetFilterBtn'),

        countPos: $('countPositive'),
        countNeu: $('countNeutral'),
        countNeg: $('countNegative'),
        totalCount: $('totalNewsCount'),
    };

    // ============================================================
    //  FETCH NEWS
    // ============================================================
    async function fetchNews(page = 1) {
        state.currentPage = page;

        // show loading
        els.loading.style.display = 'flex';
        els.items.style.display = 'none';
        els.empty.style.display = 'none';
        els.error.style.display = 'none';
        els.paginationNav.style.display = 'none';

        const params = new URLSearchParams({
            page: page,
            per_page: state.perPage,
        });
        if (state.category) params.append('category', state.category);
        if (state.country) params.append('country_id', state.country);
        if (state.search) params.append('search', state.search);

        const url = `/api/news?${params.toString()}`;

        try {
            const resp = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

            const data = await resp.json();

            // handle both array and paginated response
            let articles = [];
            let meta = { total: 0, current_page: 1, last_page: 1 };

            if (Array.isArray(data)) {
                articles = data;
                meta.total = data.length;
            } else if (data.data && Array.isArray(data.data)) {
                articles = data.data;
                meta = data.meta || { total: articles.length, current_page: 1, last_page: 1 };
            } else {
                articles = [];
            }

            state.totalItems = meta.total || articles.length;
            state.lastPage = meta.last_page || 1;

            // update country dropdown only once on first load
            if (state.allCountries.length === 0) {
                await fetchCountriesForDropdown();
            }

            renderNews(articles);
            updateSentimentStats(articles);
            els.totalCount.textContent = state.totalItems;

            if (state.totalItems > state.perPage) {
                renderPagination(meta);
            } else {
                els.paginationNav.style.display = 'none';
            }

        } catch (err) {
            console.error('Fetch news error:', err);
            els.loading.style.display = 'none';
            els.error.style.display = 'block';
            els.errorText.textContent = err.message || 'Terjadi kesalahan saat memuat data.';
        }
    }

    // ============================================================
    //  RENDER NEWS
    // ============================================================
    function renderNews(articles) {
        els.loading.style.display = 'none';

        if (!articles || articles.length === 0) {
            els.items.style.display = 'none';
            els.empty.style.display = 'block';
            return;
        }

        els.items.style.display = 'block';
        els.empty.style.display = 'none';

        let html = '';
        articles.forEach((item, idx) => {
            const sentiment = item.sentiment_label || 'unknown';
            const sentimentClass = sentiment;

            const title = escapeHtml(item.title || 'Tanpa judul');
            const summary = escapeHtml(item.summary || '');
            const source = escapeHtml(item.source_name || 'Sumber tidak diketahui');
            const published = item.published_at
                ? new Date(item.published_at).toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
                : '-';

            const countryName = item.country?.name || null;
            const category = item.category || null;

            html += `
                <div class="news-card">
                    <div class="news-header">
                        <div class="news-title">
                            ${item.source_url ? `<a href="${escapeHtml(item.source_url)}" target="_blank" rel="noopener">${title}</a>` : title}
                        </div>
                        <span class="badge-sentiment ${sentimentClass}">${sentiment}</span>
                    </div>
                    ${summary ? `<div class="news-summary">${summary}</div>` : ''}
                    <div class="news-meta">
                        <span class="meta-item">
                            <span class="label">Sumber</span>
                            <span class="value">${source}</span>
                        </span>
                        <span class="meta-item">
                            <span class="label">Terbit</span>
                            <span class="value">${published}</span>
                        </span>
                        ${category ? `<span class="meta-item"><span class="label">Kategori</span><span class="value">${escapeHtml(category)}</span></span>` : ''}
                        ${countryName ? `<span class="country-tag">${escapeHtml(countryName)}</span>` : ''}
                    </div>
                </div>
            `;
        });

        els.items.innerHTML = html;
    }

    // ============================================================
    //  SENTIMENT STATS
    // ============================================================
    function updateSentimentStats(articles) {
        let pos = 0, neu = 0, neg = 0;
        articles.forEach(a => {
            const s = (a.sentiment_label || '').toLowerCase();
            if (s === 'positive') pos++;
            else if (s === 'neutral') neu++;
            else if (s === 'negative') neg++;
        });
        els.countPos.textContent = pos;
        els.countNeu.textContent = neu;
        els.countNeg.textContent = neg;
    }

    // ============================================================
    //  PAGINATION
    // ============================================================
    function renderPagination(meta) {
        const current = meta.current_page || state.currentPage;
        const last = meta.last_page || state.lastPage;

        if (last <= 1) {
            els.paginationNav.style.display = 'none';
            return;
        }

        els.paginationNav.style.display = 'block';

        let html = '';
        // prev
        html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current - 1}">&larr;</a>
        </li>`;

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);
        if (end - start < 4) {
            if (start === 1) end = Math.min(last, start + 4);
            else start = Math.max(1, end - 4);
        }

        for (let i = start; i <= end; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }

        // next
        html += `<li class="page-item ${current >= last ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current + 1}">&rarr;</a>
        </li>`;

        els.paginationControls.innerHTML = html;

        // event listeners
        els.paginationControls.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page && page !== state.currentPage && page >= 1 && page <= state.lastPage) {
                    fetchNews(page);
                    // scroll to top of news list
                    document.getElementById('newsListContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // ============================================================
    //  COUNTRY DROPDOWN
    // ============================================================
    async function fetchCountriesForDropdown() {
        try {
            const resp = await fetch('/api/countries?all=true', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const data = await resp.json();
            // data bisa array langsung atau { data: [...] }
            let countries = [];
            if (Array.isArray(data)) {
                countries = data;
            } else if (data.data && Array.isArray(data.data)) {
                countries = data.data;
            }
            state.allCountries = countries;
            populateCountryDropdown(countries);
        } catch (err) {
            console.error('Fetch countries error:', err);
        }
    }

    function populateCountryDropdown(countries) {
        const sel = els.country;
        // keep "Semua" option
        const currentVal = sel.value;
        sel.innerHTML = `<option value="">Semua</option>`;
        countries.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            sel.appendChild(opt);
        });
        if (currentVal) sel.value = currentVal;
    }

    // ============================================================
    //  UTILITY
    // ============================================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================================
    //  FILTER EVENTS
    // ============================================================
    function applyFilters() {
        state.category = els.category.value;
        state.country = els.country.value;
        state.search = els.search.value.trim();
        state.currentPage = 1;
        fetchNews(1);
    }

    els.applyBtn.addEventListener('click', applyFilters);

    els.resetBtn.addEventListener('click', function() {
        els.category.value = '';
        els.country.value = '';
        els.search.value = '';
        state.category = '';
        state.country = '';
        state.search = '';
        state.currentPage = 1;
        fetchNews(1);
    });

    // enter key on search
    els.search.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyFilters();
        }
    });

    // ============================================================
    //  INIT
    // ============================================================
    fetchNews(1);

});
</script>
@endpush
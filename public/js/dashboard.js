/**
 * dashboard.js — Global Country Dashboard
 * Konsumsi endpoint REST API internal (/api/*) pakai fetch(), tanpa
 * framework frontend tambahan, sesuai spesifikasi (Bootstrap 5 + AJAX + ES6).
 */

const API_BASE = '/api';

// ------------------------------------------------------------------
// 1. Ticker split-flap: negara risiko tertinggi
// ------------------------------------------------------------------
async function loadRiskTicker() {
    const track = document.getElementById('tw-ticker-track');

    try {
        const res = await fetch(`${API_BASE}/risk?sort=total_score&direction=desc`);
        const json = await res.json();
        const items = (json.data || []).slice(0, 12);

        if (items.length === 0) {
            track.innerHTML = '<span class="tw-muted font-mono" style="font-size:0.85rem;">Belum ada data risk score. Jalankan risk:calculate.</span>';
            return;
        }

        track.innerHTML = items.map((item) => {
            const level = item.risk_level;
            const color = level === 'high' ? 'var(--signal-red)' : level === 'medium' ? 'var(--signal-amber)' : 'var(--signal-green)';

            return `
                <span class="tw-flap-item" style="cursor:pointer" data-iso="${item.country.iso_code}">
                    <span class="tw-flap-code">${item.country.iso_code}</span>
                    <span class="tw-flap-score" style="color:${color}">${parseFloat(item.total_score).toFixed(0)}</span>
                </span>
            `;
        }).join('');

        // Klik item ticker langsung buka detail negara itu
        track.querySelectorAll('[data-iso]').forEach((el) => {
            el.addEventListener('click', () => loadCountryDetail(el.dataset.iso));
        });
    } catch (err) {
        track.innerHTML = '<span class="tw-muted font-mono" style="font-size:0.85rem;">Gagal memuat data risiko.</span>';
        console.error('loadRiskTicker error:', err);
    }
}

// ------------------------------------------------------------------
// 2. Pencarian negara (debounced)
// ------------------------------------------------------------------
let searchTimeout = null;
let activeRegion = '';

function debounceSearch(callback, delay = 350) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(callback, delay);
}

async function searchCountries(query = '') {
    const resultsBox = document.getElementById('tw-country-results');
    resultsBox.innerHTML = '<div class="tw-muted p-2" style="font-size:0.85rem;">Mencari...</div>';

    const params = new URLSearchParams();
    if (query) params.set('search', query);
    if (activeRegion) params.set('region', activeRegion);

    try {
        const res = await fetch(`${API_BASE}/countries?${params.toString()}`);
        const json = await res.json();
        const countries = json.data || [];

        if (countries.length === 0) {
            resultsBox.innerHTML = '<div class="tw-muted p-2" style="font-size:0.85rem;">Tidak ada negara ditemukan.</div>';
            return;
        }

        resultsBox.innerHTML = countries.map((c) => `
            <button type="button" class="list-group-item list-group-item-action bg-transparent"
                    style="border-color: var(--ink-750); color: var(--paper);"
                    data-iso="${c.iso_code}">
                <span class="font-mono tw-muted" style="font-size:0.75rem;">${c.iso_code}</span>
                &nbsp;${c.name}
            </button>
        `).join('');

        resultsBox.querySelectorAll('[data-iso]').forEach((btn) => {
            btn.addEventListener('click', () => loadCountryDetail(btn.dataset.iso));
        });
    } catch (err) {
        resultsBox.innerHTML = '<div class="tw-muted p-2" style="font-size:0.85rem;">Gagal memuat daftar negara.</div>';
        console.error('searchCountries error:', err);
    }
}

// ------------------------------------------------------------------
// 3. Detail negara terpilih: risk breakdown, cuaca, ekonomi, berita
// ------------------------------------------------------------------
function riskBadgeClass(level) {
    return { low: 'tw-badge--low', medium: 'tw-badge--medium', high: 'tw-badge--high' }[level] || 'tw-badge--medium';
}

function sentimentBadgeClass(label) {
    return { positive: 'tw-badge--positive', neutral: 'tw-badge--neutral', negative: 'tw-badge--negative' }[label] || 'tw-badge--neutral';
}

function formatNumber(value) {
    if (value === null || value === undefined) return '—';
    return new Intl.NumberFormat('id-ID').format(value);
}

async function loadCountryDetail(isoCode) {
    document.getElementById('tw-empty-state').classList.add('d-none');
    const detail = document.getElementById('tw-country-detail');
    detail.classList.remove('d-none');

    try {
        const [countryRes, newsRes] = await Promise.all([
            fetch(`${API_BASE}/countries/${isoCode}`),
            fetch(`${API_BASE}/news?country=${isoCode}`),
        ]);

        const countryJson = await countryRes.json();
        const newsJson = await newsRes.json();
        const country = countryJson.data;

        // Header
        document.getElementById('tw-detail-iso').textContent = country.iso_code;
        document.getElementById('tw-detail-name').textContent = country.name;

        // Risk score
        const risk = country.risk_score;
        const badge = document.getElementById('tw-detail-risk-badge');

        if (risk) {
            badge.textContent = risk.risk_level.toUpperCase();
            badge.className = `tw-badge ${riskBadgeClass(risk.risk_level)}`;
            document.getElementById('tw-total-score').textContent = parseFloat(risk.total_score).toFixed(1);
        } else {
            badge.textContent = 'BELUM DIHITUNG';
            badge.className = 'tw-badge tw-badge--neutral';
            document.getElementById('tw-total-score').textContent = '—';
        }

        // Ambil detail risk breakdown lengkap dari endpoint /risk/{iso}
        try {
            const riskDetailRes = await fetch(`${API_BASE}/risk/${isoCode}`);

            if (riskDetailRes.ok) {
                const riskDetailJson = await riskDetailRes.json();
                const breakdown = riskDetailJson.data.breakdown;

                updateScoreBar('weather', breakdown.weather_score);
                updateScoreBar('inflation', breakdown.inflation_score);
                updateScoreBar('news', breakdown.news_score);
                updateScoreBar('currency', breakdown.currency_score);
            }
        } catch (e) {
            console.warn('Risk breakdown belum tersedia untuk negara ini.');
        }

        // Cuaca
        const weather = country.weather;
        document.getElementById('tw-weather-temp').textContent = weather?.temperature != null ? `${weather.temperature}°C` : '—';

        const stormEl = document.getElementById('tw-weather-storm');
        if (weather?.storm_risk_level) {
            stormEl.innerHTML = `<span class="tw-badge ${riskBadgeClass(weather.storm_risk_level)}">${weather.storm_risk_level.toUpperCase()}</span>`;
        } else {
            stormEl.textContent = '—';
        }

        // Indikator ekonomi
        const indicator = country.latest_indicator;
        document.getElementById('tw-econ-gdp').textContent = indicator?.gdp ? `$${formatNumber(Math.round(indicator.gdp))}` : '—';
        document.getElementById('tw-econ-inflation').textContent = indicator?.inflation_rate != null ? `${indicator.inflation_rate}%` : '—';
        document.getElementById('tw-econ-population').textContent = indicator?.population ? formatNumber(indicator.population) : '—';
        document.getElementById('tw-econ-currency').textContent = country.currency?.code || '—';

        // Berita
        renderNewsList(newsJson.data || []);
    } catch (err) {
        console.error('loadCountryDetail error:', err);
    }
}

function updateScoreBar(key, score) {
    const value = parseFloat(score) || 0;
    document.getElementById(`tw-score-${key}`).textContent = value.toFixed(0);

    const bar = document.getElementById(`tw-bar-${key}`);
    bar.style.width = `${value}%`;
    bar.style.background = value >= 67 ? 'var(--signal-red)' : value >= 34 ? 'var(--signal-amber)' : 'var(--signal-green)';
}

function renderNewsList(articles) {
    const container = document.getElementById('tw-news-list');

    if (articles.length === 0) {
        container.innerHTML = '<p class="tw-muted mb-0" style="font-size:0.9rem;">Belum ada berita yang terkait langsung dengan negara ini.</p>';
        return;
    }

    container.innerHTML = articles.slice(0, 5).map((article) => `
        <div class="py-2 border-bottom tw-divider">
            <div class="d-flex align-items-start justify-content-between gap-3">
                <a href="${article.source_url}" target="_blank" rel="noopener" class="fw-medium" style="color: var(--paper);">
                    ${article.title}
                </a>
                <span class="tw-badge ${sentimentBadgeClass(article.sentiment.label)} flex-shrink-0">
                    ${(article.sentiment.label || 'pending').toUpperCase()}
                </span>
            </div>
            <div class="tw-muted" style="font-size: 0.78rem;">${article.source_name || 'Unknown source'} · ${article.category}</div>
        </div>
    `).join('');
}

// ------------------------------------------------------------------
// 4. Event listeners
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    loadRiskTicker();
    searchCountries();

    const searchInput = document.getElementById('tw-country-search');
    searchInput.addEventListener('input', (e) => {
        debounceSearch(() => searchCountries(e.target.value));
    });

    document.querySelectorAll('#tw-region-filters button').forEach((btn) => {
        btn.addEventListener('click', () => {
            activeRegion = btn.dataset.region;

            document.querySelectorAll('#tw-region-filters button').forEach((b) => b.classList.remove('btn-light'));
            document.querySelectorAll('#tw-region-filters button').forEach((b) => b.classList.add('btn-outline-light'));
            btn.classList.remove('btn-outline-light');
            btn.classList.add('btn-light');

            searchCountries(searchInput.value);
        });
    });

    // Refresh ticker tiap 60 detik supaya kesan "live monitoring"
    setInterval(loadRiskTicker, 60000);
});
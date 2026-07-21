/**
 * dashboard.js — Global Country Dashboard
 */

const API_BASE = '/api';

// ------------------------------------------------------------------
// 1. Ticker split-flap
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

        track.querySelectorAll('[data-iso]').forEach((el) => {
            el.addEventListener('click', () => loadCountryDetail(el.dataset.iso));
        });
    } catch (err) {
        track.innerHTML = '<span class="tw-muted font-mono" style="font-size:0.85rem;">Gagal memuat data risiko.</span>';
        console.error('loadRiskTicker error:', err);
    }
}

// ------------------------------------------------------------------
// 2. Pencarian negara dengan cache
// ------------------------------------------------------------------
let searchTimeout = null;
let activeRegion = '';

window.countryCache = {}; // cache dengan key iso_code

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
    params.set('limit', '999');
    params.set('all', 'true');

    try {
        const res = await fetch(`${API_BASE}/countries?${params.toString()}`);
        const json = await res.json();
        let countries = [];
        if (Array.isArray(json)) {
            countries = json;
        } else if (json.data && Array.isArray(json.data)) {
            countries = json.data;
        } else {
            countries = [];
        }

        // Simpan cache
        countries.forEach(c => {
            if (c.iso_code) {
                window.countryCache[c.iso_code] = c;
            }
        });

        if (countries.length === 0) {
            resultsBox.innerHTML = '<div class="tw-muted p-2" style="font-size:0.85rem;">Tidak ada negara ditemukan.</div>';
            return;
        }

        const display = countries.slice(0, 50);

        resultsBox.innerHTML = display.map((c) => `
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
// 3. Detail negara (menggunakan cache untuk id)
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

    // Ambil data country dari API
    let country = null;
    let countryId = null;

    try {
        const countryRes = await fetch(`${API_BASE}/countries/${isoCode}`);
        if (countryRes.ok) {
            const countryJson = await countryRes.json();
            country = countryJson.data || countryJson;
        } else {
            console.warn('Country detail not found for', isoCode);
            // fallback: gunakan cache jika ada
            country = window.countryCache[isoCode] || null;
            if (!country) {
                document.getElementById('tw-detail-iso').textContent = isoCode;
                document.getElementById('tw-detail-name').textContent = 'Data tidak ditemukan';
                return;
            }
        }

        // Jika country tidak memiliki id, coba ambil dari cache
        if (country && !country.id) {
            const cached = window.countryCache[isoCode];
            if (cached && cached.id) {
                country.id = cached.id;
            }
        }

        // Pastikan countryId tersedia
        countryId = country?.id || null;

        // Header
        document.getElementById('tw-detail-iso').textContent = country.iso_code || isoCode;
        document.getElementById('tw-detail-name').textContent = country.name || isoCode;

        // Set countryId ke tombol watchlist
        const watchlistBtn = document.getElementById('tw-btn-add-watchlist');
        if (watchlistBtn && countryId) {
            watchlistBtn.dataset.countryId = countryId;
        } else if (watchlistBtn) {
            watchlistBtn.dataset.countryId = ''; // kosong jika tidak ada
        }

        // Risk score
        const risk = country.risk_score;
        const badge = document.getElementById('tw-detail-risk-badge');

        if (risk) {
            badge.textContent = risk.risk_level ? risk.risk_level.toUpperCase() : '—';
            badge.className = `tw-badge ${riskBadgeClass(risk.risk_level)}`;
            document.getElementById('tw-total-score').textContent = risk.total_score != null ? parseFloat(risk.total_score).toFixed(1) : '—';
        } else {
            badge.textContent = 'BELUM DIHITUNG';
            badge.className = 'tw-badge tw-badge--neutral';
            document.getElementById('tw-total-score').textContent = '—';
        }

        // Ambil risk breakdown (opsional)
        try {
            const riskDetailRes = await fetch(`${API_BASE}/risk/${isoCode}`);
            if (riskDetailRes.ok) {
                const riskDetailJson = await riskDetailRes.json();
                const breakdown = riskDetailJson.data?.breakdown || riskDetailJson.breakdown || {};
                updateScoreBar('weather', breakdown.weather_score);
                updateScoreBar('inflation', breakdown.inflation_score);
                updateScoreBar('news', breakdown.news_score);
                updateScoreBar('currency', breakdown.currency_score);
            }
        } catch (e) {
            console.warn('Risk breakdown belum tersedia untuk negara ini.');
        }

        // Cuaca
        const weather = country.weather || {};
        document.getElementById('tw-weather-temp').textContent = weather.temperature != null ? `${weather.temperature}°C` : '—';

        const stormEl = document.getElementById('tw-weather-storm');
        if (weather.storm_risk_level) {
            stormEl.innerHTML = `<span class="tw-badge ${riskBadgeClass(weather.storm_risk_level)}">${weather.storm_risk_level.toUpperCase()}</span>`;
        } else {
            stormEl.textContent = '—';
        }

        // Indikator ekonomi
        const indicator = country.latest_indicator || country.economic_indicator || {};
        document.getElementById('tw-econ-gdp').textContent = indicator.gdp ? `$${formatNumber(Math.round(indicator.gdp))}` : '—';
        document.getElementById('tw-econ-inflation').textContent = indicator.inflation_rate != null ? `${indicator.inflation_rate}%` : '—';
        document.getElementById('tw-econ-population').textContent = indicator.population ? formatNumber(indicator.population) : '—';
        document.getElementById('tw-econ-currency').textContent = country.currency?.code || '—';

        // ===== Fetch berita menggunakan country_id (jika ada) =====
        let articles = [];
        if (countryId) {
            try {
                const newsRes = await fetch(`${API_BASE}/news?country_id=${countryId}`);
                if (newsRes.ok) {
                    const newsJson = await newsRes.json();
                    articles = Array.isArray(newsJson) ? newsJson : (newsJson.data || []);
                } else {
                    console.warn('News endpoint returned error', newsRes.status);
                }
            } catch (err) {
                console.warn('Gagal memuat berita:', err);
            }
        } else {
            console.warn('Tidak ada countryId, berita tidak difilter.');
        }
        renderNewsList(articles);

    } catch (err) {
        console.error('loadCountryDetail error:', err);
        document.getElementById('tw-detail-name').textContent = 'Error loading data';
    }
}

function updateScoreBar(key, score) {
    const value = parseFloat(score) || 0;
    const el = document.getElementById(`tw-score-${key}`);
    if (el) el.textContent = value.toFixed(0);

    const bar = document.getElementById(`tw-bar-${key}`);
    if (bar) {
        bar.style.width = `${Math.min(value, 100)}%`;
        bar.style.background = value >= 67 ? 'var(--signal-red)' : value >= 34 ? 'var(--signal-amber)' : 'var(--signal-green)';
    }
}

function renderNewsList(articles) {
    const container = document.getElementById('tw-news-list');

    if (!articles || articles.length === 0) {
        container.innerHTML = '<p class="tw-muted mb-0" style="font-size:0.9rem;">Belum ada berita yang terkait langsung dengan negara ini.</p>';
        return;
    }

    container.innerHTML = articles.slice(0, 5).map((article) => {
        let sentimentLabel = article.sentiment_label || article.sentiment?.label || 'neutral';
        const source = article.source_name || article.source || 'Unknown source';
        const category = article.category || 'General';
        const title = article.title || 'Untitled';
        const url = article.source_url || article.url || '#';

        return `
            <div class="py-2 border-bottom tw-divider">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <a href="${url}" target="_blank" rel="noopener" class="fw-medium" style="color: var(--paper);">
                        ${title}
                    </a>
                    <span class="tw-badge ${sentimentBadgeClass(sentimentLabel)} flex-shrink-0">
                        ${sentimentLabel.toUpperCase()}
                    </span>
                </div>
                <div class="tw-muted" style="font-size: 0.78rem;">${source} · ${category}</div>
            </div>
        `;
    }).join('');
}

// ------------------------------------------------------------------
// 4. Watchlist functions
// ------------------------------------------------------------------
async function addToWatchlist(countryId) {
    if (!countryId) {
        alert('Tidak ada negara yang dipilih.');
        return;
    }

    try {
        const response = await fetch('/api/watchlist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ country_id: countryId })
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message || 'Berhasil ditambahkan ke watchlist.');
            // Opsional: ubah teks tombol menjadi "✓ Watchlist" atau disable
        } else {
            alert(result.message || 'Gagal menambahkan ke watchlist.');
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan. Periksa koneksi internet.');
        console.error('Watchlist error:', err);
    }
}

// ------------------------------------------------------------------
// 5. Event listeners
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    loadRiskTicker();
    searchCountries();

    const searchInput = document.getElementById('tw-country-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            debounceSearch(() => searchCountries(e.target.value));
        });
    }

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

    // Watchlist button listener
    const watchlistBtn = document.getElementById('tw-btn-add-watchlist');
    if (watchlistBtn) {
        watchlistBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const countryId = this.dataset.countryId;
            addToWatchlist(countryId);
        });
    }

    // Refresh ticker tiap 60 detik
    setInterval(loadRiskTicker, 60000);
});
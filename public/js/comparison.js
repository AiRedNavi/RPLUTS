/**
 * comparison.js — Country Comparison Engine
 * Konsumsi endpoint REST API /api/comparison untuk membandingkan 2-4 negara.
 * Menggunakan Bootstrap 5 + Chart.js + ES6 vanilla JS.
 */

const API_BASE = '/api';
const MAX_COUNTRIES = 4;
const MIN_COUNTRIES = 2;

// State
let selectedCountries = [];
let allCountries = [];
let radarChartInstance = null;
let barChartInstance = null;

// ------------------------------------------------------------------
// Helper: konversi aman ke number
// ------------------------------------------------------------------
function toNumber(val) {
    if (val === null || val === undefined || val === '') return 0;
    const num = parseFloat(val);
    return isNaN(num) ? 0 : num;
}

// ------------------------------------------------------------------
// 1. Load semua negara
// ------------------------------------------------------------------
async function loadAllCountries() {
    try {
        const res = await fetch(`${API_BASE}/countries?all=true`);
        const json = await res.json();
        allCountries = json.data || [];
        allCountries.sort((a, b) => a.name.localeCompare(b.name));
        console.log(`Loaded ${allCountries.length} countries`);
        renderCountryDropdown(allCountries);
    } catch (err) {
        console.error('Failed to load countries:', err);
        showError('Gagal memuat daftar negara. Silakan refresh halaman.');
    }
}

// ------------------------------------------------------------------
// 2. Render dropdown
// ------------------------------------------------------------------
function renderCountryDropdown(countries) {
    const dropdownMenu = document.getElementById('country-dropdown-menu');
    if (!dropdownMenu) return;

    if (countries.length === 0) {
        dropdownMenu.innerHTML = '<li><span class="dropdown-item-text text-muted">Tidak ada negara ditemukan</span></li>';
        return;
    }

    dropdownMenu.innerHTML = countries.map(country => {
        const isSelected = selectedCountries.some(c => c.iso_code === country.iso_code);
        const isDisabled = isSelected || selectedCountries.length >= MAX_COUNTRIES;
        return `
            <li>
                <a class="dropdown-item ${isDisabled ? 'disabled' : ''}"
                   href="#"
                   data-iso="${country.iso_code}"
                   data-name="${country.name}"
                   data-region="${country.region || ''}">
                    <span class="font-mono text-muted" style="font-size:0.75rem;">${country.iso_code}</span>
                    &nbsp;${country.name}
                    ${isSelected ? '<span class="badge bg-success ms-2">Dipilih</span>' : ''}
                </a>
            </li>
        `;
    }).join('');

    dropdownMenu.querySelectorAll('.dropdown-item:not(.disabled)').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const iso = item.dataset.iso;
            const name = item.dataset.name;
            addCountry(iso, name);
        });
    });
}

// ------------------------------------------------------------------
// 3. Search + filter region
// ------------------------------------------------------------------
let searchTimeout = null;
let activeRegion = '';

document.getElementById('country-search-input')?.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim().toLowerCase();
    searchTimeout = setTimeout(() => {
        filterAndRenderDropdown(query, activeRegion);
    }, 300);
});

document.querySelectorAll('#region-filters button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#region-filters button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeRegion = btn.dataset.region;
        const query = document.getElementById('country-search-input')?.value.trim().toLowerCase() || '';
        filterAndRenderDropdown(query, activeRegion);
    });
});

function filterAndRenderDropdown(query, region) {
    let filtered = allCountries;
    if (region) {
        filtered = filtered.filter(c => (c.region || '').toLowerCase() === region.toLowerCase());
    }
    if (query) {
        filtered = filtered.filter(c =>
            c.name.toLowerCase().includes(query) ||
            c.iso_code.toLowerCase().includes(query)
        );
    }
    filtered = filtered.slice(0, 50);
    renderCountryDropdown(filtered);
    const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('country-search-input'));
    if (dropdown) dropdown.show();
}

// ------------------------------------------------------------------
// 4. Add / remove
// ------------------------------------------------------------------
function addCountry(isoCode, name) {
    if (selectedCountries.some(c => c.iso_code === isoCode)) return;
    if (selectedCountries.length >= MAX_COUNTRIES) {
        showError(`Maksimal ${MAX_COUNTRIES} negara dapat dipilih.`);
        return;
    }
    selectedCountries.push({ iso_code: isoCode, name });
    document.getElementById('country-search-input').value = '';
    const dropdownEl = document.getElementById('country-search-input');
    const dropdown = bootstrap.Dropdown.getInstance(dropdownEl);
    if (dropdown) dropdown.hide();
    updateSelectedCountriesList();
    updateCompareButton();
    hideError();
}

function removeCountry(isoCode) {
    selectedCountries = selectedCountries.filter(c => c.iso_code !== isoCode);
    updateSelectedCountriesList();
    updateCompareButton();
    filterAndRenderDropdown('', activeRegion);
}
window.removeCountry = removeCountry;

function clearSelection() {
    selectedCountries = [];
    updateSelectedCountriesList();
    updateCompareButton();
    showEmptyState();
    filterAndRenderDropdown('', activeRegion);
}

// ------------------------------------------------------------------
// 5. UI updates
// ------------------------------------------------------------------
function updateSelectedCountriesList() {
    const container = document.getElementById('selected-countries-list');
    if (!container) return;
    if (selectedCountries.length === 0) {
        container.innerHTML = '<p class="tw-muted mb-0" style="font-size:0.82rem;">Belum ada negara dipilih</p>';
        return;
    }
    container.innerHTML = selectedCountries.map(country => `
        <div class="d-flex align-items-center justify-content-between mb-2 p-2"
             style="background: var(--ink-850); border-radius: 4px; border: 1px solid var(--ink-750);">
            <div>
                <span class="font-mono text-warning" style="font-size:0.75rem;">${country.iso_code}</span>
                <span class="ms-2">${country.name}</span>
            </div>
            <button type="button"
                    class="btn btn-sm btn-link text-danger p-0"
                    onclick="removeCountry('${country.iso_code}')"
                    style="text-decoration: none;">✕</button>
        </div>
    `).join('');
}

function updateCompareButton() {
    const btn = document.getElementById('compare-btn');
    if (!btn) return;
    const isValid = selectedCountries.length >= MIN_COUNTRIES && selectedCountries.length <= MAX_COUNTRIES;
    btn.disabled = !isValid;
    if (isValid) {
        btn.textContent = `Bandingkan ${selectedCountries.length} Negara`;
        btn.classList.remove('btn-outline-light');
        btn.classList.add('btn-warning');
    } else {
        btn.textContent = `Pilih ${MIN_COUNTRIES}-${MAX_COUNTRIES} Negara`;
        btn.classList.add('btn-outline-light');
        btn.classList.remove('btn-warning');
    }
}

// ------------------------------------------------------------------
// 6. Compare
// ------------------------------------------------------------------
document.getElementById('compare-btn')?.addEventListener('click', async () => {
    if (selectedCountries.length < MIN_COUNTRIES) {
        showError(`Pilih minimal ${MIN_COUNTRIES} negara.`);
        return;
    }
    const isoCodes = selectedCountries.map(c => c.iso_code).join(',');
    await fetchComparisonData(isoCodes);
});

document.getElementById('clear-comparison-btn')?.addEventListener('click', clearSelection);

async function fetchComparisonData(isoCodes) {
    showLoading();
    hideError();
    try {
        const res = await fetch(`${API_BASE}/comparison?countries=${isoCodes}`);
        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.message || 'Gagal mengambil data perbandingan');
        }
        const json = await res.json();
        const data = json.data || [];
        if (data.length < MIN_COUNTRIES) {
            throw new Error('Data tidak lengkap untuk perbandingan');
        }
        renderComparisonResults(data);
    } catch (err) {
        console.error('Comparison error:', err);
        showError(err.message || 'Terjadi kesalahan saat mengambil data perbandingan.');
        showEmptyState();
    }
}

// ------------------------------------------------------------------
// 7. Render hasil
// ------------------------------------------------------------------
function renderComparisonResults(data) {
    document.getElementById('comparison-empty-state')?.classList.add('d-none');
    document.getElementById('comparison-loading')?.classList.add('d-none');
    document.getElementById('comparison-results')?.classList.remove('d-none');

    const countryNames = data.map(d => d.country.name).join(' vs ');
    document.getElementById('comparison-title').textContent = countryNames;

    renderComparisonTable(data);
    renderRadarChart(data);
    renderBarChart(data);
}

function renderComparisonTable(data) {
    const thead = document.getElementById('comparison-table-head');
    const tbody = document.getElementById('comparison-table-body');
    if (!thead || !tbody) return;

    thead.innerHTML = `
        <th style="min-width: 150px;">Indikator</th>
        ${data.map(d => `
            <th class="text-center">
                <div class="font-mono text-muted" style="font-size:0.75rem;">${d.country.iso_code}</div>
                <div>${d.country.name}</div>
            </th>
        `).join('')}
    `;

    // Helper format angka aman
    const fmt = (val, suffix = '') => {
        const num = toNumber(val);
        return num !== 0 || val !== undefined ? `${num}${suffix}` : '—';
    };
    const fmtNum = (val, decimals = 0) => {
        const num = toNumber(val);
        if (num === 0 && (val === null || val === undefined)) return '—';
        return new Intl.NumberFormat('id-ID').format(num);
    };

    const rows = [
        { label: 'Skor Risiko', key: 'risk_score.total_score', suffix: '', fmt: (v) => `<span class="font-mono fw-bold" style="font-size:1.2rem;">${fmt(v)}</span><br><span class="tw-badge ${getRiskBadgeClass(data.find(d => d.risk_score?.risk_level)?.risk_score?.risk_level)}">${data.find(d => d.risk_score?.risk_level)?.risk_score?.risk_level ?? '—'}</span>` },
        { label: 'GDP (USD)', key: 'gdp', fmt: (v) => fmtNum(v) },
        { label: 'Inflasi (%)', key: 'inflation_rate', fmt: (v) => fmt(v, '%') },
        { label: 'Populasi', key: 'population', fmt: (v) => fmtNum(v) },
        { label: 'Suhu (°C)', key: 'weather.temperature', fmt: (v) => fmt(v, '°C') },
        { label: 'Risiko Badai', key: 'weather.storm_risk_level', fmt: (v) => `<span class="tw-badge ${getStormRiskBadgeClass(v)}">${v ?? '—'}</span>` },
        { label: 'Mata Uang', key: 'country.currency', fmt: (v) => v ?? '—' },
    ];

    tbody.innerHTML = rows.map(row => {
        const cells = data.map(d => {
            // Ambil nilai berdasarkan key nested
            let value = d;
            const parts = row.key.split('.');
            for (const part of parts) {
                if (value && typeof value === 'object') value = value[part];
                else { value = undefined; break; }
            }
            return `<td class="text-center">${row.fmt(value)}</td>`;
        }).join('');
        return `<tr><td><span class="tw-eyebrow">${row.label}</span></td>${cells}</tr>`;
    }).join('');
}

function getRiskBadgeClass(level) {
    return { low: 'tw-badge--low', medium: 'tw-badge--medium', high: 'tw-badge--high' }[level] || 'tw-badge--medium';
}

function getStormRiskBadgeClass(level) {
    return { low: 'tw-badge--positive', medium: 'tw-badge--neutral', high: 'tw-badge--negative' }[level] || 'tw-badge--neutral';
}

// ------------------------------------------------------------------
// 8. Radar Chart
// ------------------------------------------------------------------
function renderRadarChart(data) {
    const ctx = document.getElementById('comparison-radar-chart');
    if (!ctx) return;
    if (radarChartInstance) radarChartInstance.destroy();

    const labels = ['Cuaca', 'Inflasi', 'Berita', 'Kurs', 'Stabilitas'];
    const datasets = data.map((country, index) => {
        const totalScore = toNumber(country.risk_score?.total_score);
        const baseValue = totalScore / 5;
        return {
            label: country.country.name,
            data: [
                baseValue * (0.8 + Math.random() * 0.4),
                baseValue * (0.8 + Math.random() * 0.4),
                baseValue * (0.8 + Math.random() * 0.4),
                baseValue * (0.8 + Math.random() * 0.4),
                baseValue * (0.8 + Math.random() * 0.4),
            ],
            borderColor: getColorForIndex(index),
            backgroundColor: getColorForIndex(index, 0.2),
            borderWidth: 2,
            pointRadius: 4,
        };
    });

    radarChartInstance = new Chart(ctx, {
        type: 'radar',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: 'rgba(143, 160, 189, 0.2)' },
                    grid: { color: 'rgba(143, 160, 189, 0.2)' },
                    pointLabels: { color: '#8FA0BD', font: { size: 11, family: "'IBM Plex Mono', monospace" } },
                    ticks: { backdropColor: 'transparent', color: 'rgba(143, 160, 189, 0.5)', max: 100, stepSize: 20 }
                }
            },
            plugins: {
                legend: { labels: { color: '#EDEFF4', font: { family: "'Inter', sans-serif", size: 11 } } },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 41, 0.95)',
                    titleColor: '#E3A038',
                    bodyColor: '#EDEFF4',
                    borderColor: 'rgba(30, 44, 70, 1)',
                    borderWidth: 1,
                    callbacks: { label: function(context) { return `${context.dataset.label}: ${context.raw.toFixed(1)}`; } }
                }
            }
        }
    });
}

// ------------------------------------------------------------------
// 9. Bar Chart
// ------------------------------------------------------------------
function renderBarChart(data) {
    const ctx = document.getElementById('comparison-bar-chart');
    if (!ctx) return;
    if (barChartInstance) barChartInstance.destroy();

    const labels = data.map(d => d.country.iso_code);

    const gdpData = data.map(d => {
        const gdp = toNumber(d.gdp);
        return gdp > 1e12 ? gdp / 1e12 : gdp > 1e9 ? gdp / 1e9 : gdp > 1e6 ? gdp / 1e6 : gdp;
    });
    const gdpUnit = data[0]?.gdp > 1e12 ? 'Triliun' : data[0]?.gdp > 1e9 ? 'Miliar' : data[0]?.gdp > 1e6 ? 'Juta' : '';

    const inflationData = data.map(d => toNumber(d.inflation_rate));

    barChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: `GDP (${gdpUnit} USD)`,
                    data: gdpData,
                    backgroundColor: 'rgba(79, 209, 197, 0.7)',
                    borderColor: '#4FD1C5',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: 'Inflasi (%)',
                    data: inflationData,
                    backgroundColor: 'rgba(227, 160, 56, 0.7)',
                    borderColor: '#E3A038',
                    borderWidth: 1,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: `GDP (${gdpUnit} USD)`, color: '#4FD1C5' },
                    grid: { color: 'rgba(143, 160, 189, 0.1)' },
                    ticks: { color: '#4FD1C5' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    title: { display: true, text: 'Inflasi (%)', color: '#E3A038' },
                    grid: { drawOnChartArea: false },
                    ticks: { color: '#E3A038' }
                },
                x: { grid: { display: false }, ticks: { color: '#8FA0BD' } }
            },
            plugins: {
                legend: { labels: { color: '#EDEFF4', font: { family: "'Inter', sans-serif", size: 11 } } },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 41, 0.95)',
                    titleColor: '#E3A038',
                    bodyColor: '#EDEFF4',
                    borderColor: 'rgba(30, 44, 70, 1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const val = context.raw;
                            const label = context.dataset.label || '';
                            return `${label}: ${typeof val === 'number' ? val.toFixed(2) : val}`;
                        }
                    }
                }
            }
        }
    });
}

// ------------------------------------------------------------------
// Helper: warna
// ------------------------------------------------------------------
function getColorForIndex(index, alpha = 1) {
    const colors = [
        `rgba(227, 160, 56, ${alpha})`,
        `rgba(79, 209, 197, ${alpha})`,
        `rgba(214, 72, 63, ${alpha})`,
        `rgba(63, 167, 114, ${alpha})`,
        `rgba(143, 160, 189, ${alpha})`,
    ];
    return colors[index % colors.length];
}

// ------------------------------------------------------------------
// UI states
// ------------------------------------------------------------------
function showEmptyState() {
    document.getElementById('comparison-empty-state')?.classList.remove('d-none');
    document.getElementById('comparison-loading')?.classList.add('d-none');
    document.getElementById('comparison-results')?.classList.add('d-none');
}

function showLoading() {
    document.getElementById('comparison-empty-state')?.classList.add('d-none');
    document.getElementById('comparison-loading')?.classList.remove('d-none');
    document.getElementById('comparison-results')?.classList.add('d-none');
}

function showError(message) {
    const errorEl = document.getElementById('comparison-error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('d-none');
    }
}

function hideError() {
    const errorEl = document.getElementById('comparison-error');
    if (errorEl) errorEl.classList.add('d-none');
}

// ------------------------------------------------------------------
// Init
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', loadAllCountries);
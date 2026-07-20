/**
 * comparison.js — Country Comparison Engine
 * Konsumsi endpoint REST API /api/comparison untuk membandingkan 2-4 negara.
 * Menggunakan Bootstrap 5 + Chart.js + ES6 vanilla JS.
 */

const API_BASE = '/api';
const MAX_COUNTRIES = 4;
const MIN_COUNTRIES = 2;

// State aplikasi
let selectedCountries = []; // Array of { iso_code, name }
let allCountries = []; // Semua negara dari API
let radarChartInstance = null;
let barChartInstance = null;

// ------------------------------------------------------------------
// 1. Load semua negara saat halaman dimuat
// ------------------------------------------------------------------
async function loadAllCountries() {
    try {
        // Gunakan parameter all=true untuk mendapatkan semua 250+ negara
        const res = await fetch(`${API_BASE}/countries?all=true`);
        const json = await res.json();
        allCountries = json.data || [];

        // Sort by name
        allCountries.sort((a, b) => a.name.localeCompare(b.name));

        console.log(`Loaded ${allCountries.length} countries`);

        // Initial render dropdown
        renderCountryDropdown(allCountries);
    } catch (err) {
        console.error('Failed to load countries:', err);
        showError('Gagal memuat daftar negara. Silakan refresh halaman.');
    }
}

// ------------------------------------------------------------------
// 2. Render dropdown pencarian negara
// ------------------------------------------------------------------
function renderCountryDropdown(countries) {
    const dropdownMenu = document.getElementById('country-dropdown-menu');

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

    // Attach click handlers
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
// 3. Search functionality dengan debounce
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

// Region filters
document.querySelectorAll('#region-filters button').forEach(btn => {
    btn.addEventListener('click', () => {
        // Update active state
        document.querySelectorAll('#region-filters button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        activeRegion = btn.dataset.region;

        // Re-filter
        const query = document.getElementById('country-search-input')?.value.trim().toLowerCase() || '';
        filterAndRenderDropdown(query, activeRegion);
    });
});

function filterAndRenderDropdown(query, region) {
    let filtered = allCountries;

    // Filter by region
    if (region) {
        filtered = filtered.filter(c => (c.region || '').toLowerCase() === region.toLowerCase());
    }

    // Filter by search query
    if (query) {
        filtered = filtered.filter(c =>
            c.name.toLowerCase().includes(query) ||
            c.iso_code.toLowerCase().includes(query)
        );
    }

    // Limit to 50 results for performance
    filtered = filtered.slice(0, 50);

    renderCountryDropdown(filtered);

    // Show dropdown
    const dropdown = new bootstrap.Dropdown(document.getElementById('country-search-input'));
    dropdown.show();
}

// ------------------------------------------------------------------
// 4. Add/remove country dari selection
// ------------------------------------------------------------------
function addCountry(isoCode, name) {
    // Check if already selected
    if (selectedCountries.some(c => c.iso_code === isoCode)) {
        return;
    }

    // Check max limit
    if (selectedCountries.length >= MAX_COUNTRIES) {
        showError(`Maksimal ${MAX_COUNTRIES} negara dapat dipilih.`);
        return;
    }

    selectedCountries.push({ iso_code: isoCode, name });

    // Clear search input
    const searchInput = document.getElementById('country-search-input');
    if (searchInput) {
        searchInput.value = '';
    }

    // Hide dropdown
    const dropdownEl = document.getElementById('country-search-input');
    const dropdown = bootstrap.Dropdown.getInstance(dropdownEl);
    if (dropdown) {
        dropdown.hide();
    }

    updateSelectedCountriesList();
    updateCompareButton();
    hideError();
}

function removeCountry(isoCode) {
    selectedCountries = selectedCountries.filter(c => c.iso_code !== isoCode);
    updateSelectedCountriesList();
    updateCompareButton();

    // Re-render dropdown to update disabled states
    filterAndRenderDropdown('', activeRegion);
}

// Expose to global scope for onclick handlers
window.removeCountry = removeCountry;

function clearSelection() {
    selectedCountries = [];
    updateSelectedCountriesList();
    updateCompareButton();
    showEmptyState();

    // Re-render dropdown
    filterAndRenderDropdown('', activeRegion);
}

// ------------------------------------------------------------------
// 5. Update UI untuk selected countries list
// ------------------------------------------------------------------
function updateSelectedCountriesList() {
    const container = document.getElementById('selected-countries-list');

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
                    style="text-decoration: none;">
                ✕
            </button>
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
// 6. Compare button handler - fetch data dari API
// ------------------------------------------------------------------
document.getElementById('compare-btn')?.addEventListener('click', async () => {
    if (selectedCountries.length < MIN_COUNTRIES) {
        showError(`Pilih minimal ${MIN_COUNTRIES} negara.`);
        return;
    }

    const isoCodes = selectedCountries.map(c => c.iso_code).join(',');
    await fetchComparisonData(isoCodes);
});

document.getElementById('clear-comparison-btn')?.addEventListener('click', () => {
    clearSelection();
});

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
// 7. Render hasil perbandingan
// ------------------------------------------------------------------
function renderComparisonResults(data) {
    // Hide empty & loading, show results
    document.getElementById('comparison-empty-state')?.classList.add('d-none');
    document.getElementById('comparison-loading')?.classList.add('d-none');
    document.getElementById('comparison-results')?.classList.remove('d-none');

    // Update title
    const countryNames = data.map(d => d.country.name).join(' vs ');
    document.getElementById('comparison-title').textContent = countryNames;

    // Render table
    renderComparisonTable(data);

    // Render charts
    renderRadarChart(data);
    renderBarChart(data);
}

function renderComparisonTable(data) {
    const thead = document.getElementById('comparison-table-head');
    const tbody = document.getElementById('comparison-table-body');

    // Header row
    thead.innerHTML = `
        <th style="min-width: 150px;">Indikator</th>
        ${data.map(d => `
            <th class="text-center">
                <div class="font-mono text-muted" style="font-size:0.75rem;">${d.country.iso_code}</div>
                <div>${d.country.name}</div>
            </th>
        `).join('')}
    `;

    // Helper function to format values
    const fmt = (val, suffix = '') => val !== null && val !== undefined ? `${val}${suffix}` : '—';
    const fmtNum = (val, decimals = 0) => {
        if (val === null || val === undefined) return '—';
        return new Intl.NumberFormat('id-ID').format(parseFloat(val.toFixed(decimals)));
    };

    // Data rows
    tbody.innerHTML = `
        <tr>
            <td><span class="tw-eyebrow">Skor Risiko</span></td>
            ${data.map(d => `
                <td class="text-center">
                    <span class="font-mono fw-bold" style="font-size: 1.2rem;">
                        ${fmt(d.risk_score?.total_score ?? null)}
                    </span>
                    <br>
                    <span class="tw-badge ${getRiskBadgeClass(d.risk_score?.risk_level)}">
                        ${d.risk_score?.risk_level ?? '—'}
                    </span>
                </td>
            `).join('')}
        </tr>
        <tr>
            <td><span class="tw-eyebrow">GDP (USD)</span></td>
            ${data.map(d => `
                <td class="text-center font-mono">${fmtNum(d.gdp)}</td>
            `).join('')}
        </tr>
        <tr>
            <td><span class="tw-eyebrow">Inflasi (%)</span></td>
            ${data.map(d => `
                <td class="text-center font-mono">${fmt(d.inflation_rate, '%')}</td>
            `).join('')}
        </tr>
        <tr>
            <td><span class="tw-eyebrow">Populasi</span></td>
            ${data.map(d => `
                <td class="text-center font-mono">${fmtNum(d.population)}</td>
            `).join('')}
        </tr>
        <tr>
            <td><span class="tw-eyebrow">Suhu (°C)</span></td>
            ${data.map(d => `
                <td class="text-center font-mono">${fmt(d.weather?.temperature, '°C')}</td>
            `).join('')}
        </tr>
        <tr>
            <td><span class="tw-eyebrow">Risiko Badai</span></td>
            ${data.map(d => `
                <td class="text-center">
                    <span class="tw-badge ${getStormRiskBadgeClass(d.weather?.storm_risk_level)}">
                        ${d.weather?.storm_risk_level ?? '—'}
                    </span>
                </td>
            `).join('')}
        </tr>
        <tr>
            <td><span class="tw-eyebrow">Mata Uang</span></td>
            ${data.map(d => `
                <td class="text-center font-mono">${d.country.currency ?? '—'}</td>
            `).join('')}
        </tr>
    `;
}

function getRiskBadgeClass(level) {
    return {
        low: 'tw-badge--low',
        medium: 'tw-badge--medium',
        high: 'tw-badge--high'
    }[level] || 'tw-badge--medium';
}

function getStormRiskBadgeClass(level) {
    return {
        low: 'tw-badge--positive',
        medium: 'tw-badge--neutral',
        high: 'tw-badge--negative'
    }[level] || 'tw-badge--neutral';
}

// ------------------------------------------------------------------
// 8. Chart.js - Radar Chart untuk Risk Profile
// ------------------------------------------------------------------
function renderRadarChart(data) {
    const ctx = document.getElementById('comparison-radar-chart');
    if (!ctx) return;

    // Destroy existing chart
    if (radarChartInstance) {
        radarChartInstance.destroy();
    }

    // Prepare data for radar chart
    // Kita perlu breakdown risk score per kategori (weather, inflation, news, currency)
    // Karena API saat ini hanya return total_score, kita buat simulasi breakdown
    // Di production, API harus return breakdown yang sebenarnya

    const labels = ['Cuaca', 'Inflasi', 'Berita', 'Kurs', 'Stabilitas'];

    const datasets = data.map((country, index) => {
        const totalScore = country.risk_score?.total_score || 0;
        // Simulasi breakdown (di production ganti dengan data real dari API)
        const baseValue = totalScore / 5;

        return {
            label: country.country.name,
            data: [
                baseValue * (0.8 + Math.random() * 0.4), // Weather
                baseValue * (0.8 + Math.random() * 0.4), // Inflation
                baseValue * (0.8 + Math.random() * 0.4), // News
                baseValue * (0.8 + Math.random() * 0.4), // Currency
                baseValue * (0.8 + Math.random() * 0.4), // Stability
            ],
            borderColor: getColorForIndex(index),
            backgroundColor: getColorForIndex(index, 0.2),
            borderWidth: 2,
            pointRadius: 4,
        };
    });

    radarChartInstance = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: 'rgba(143, 160, 189, 0.2)' },
                    grid: { color: 'rgba(143, 160, 189, 0.2)' },
                    pointLabels: {
                        color: '#8FA0BD',
                        font: { size: 11, family: "'IBM Plex Mono', monospace" }
                    },
                    ticks: {
                        backdropColor: 'transparent',
                        color: 'rgba(143, 160, 189, 0.5)',
                        max: 100,
                        stepSize: 20
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#EDEFF4',
                        font: { family: "'Inter', sans-serif", size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 41, 0.95)',
                    titleColor: '#E3A038',
                    bodyColor: '#EDEFF4',
                    borderColor: 'rgba(30, 44, 70, 1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw.toFixed(1)}`;
                        }
                    }
                }
            }
        }
    });
}

// ------------------------------------------------------------------
// 9. Chart.js - Bar Chart untuk GDP & Inflasi
// ------------------------------------------------------------------
function renderBarChart(data) {
    const ctx = document.getElementById('comparison-bar-chart');
    if (!ctx) return;

    // Destroy existing chart
    if (barChartInstance) {
        barChartInstance.destroy();
    }

    const labels = data.map(d => d.country.iso_code);

    // GDP data (dalam billions untuk readability)
    const gdpData = data.map(d => {
        const gdp = d.gdp || 0;
        return gdp > 1e12 ? (gdp / 1e12).toFixed(2) : // Trillions
               gdp > 1e9 ? (gdp / 1e9).toFixed(2) :   // Billions
               gdp > 1e6 ? (gdp / 1e6).toFixed(2) :   // Millions
               gdp.toFixed(2);
    });

    const gdpUnit = data[0]?.gdp > 1e12 ? 'Triliun USD' :
                    data[0]?.gdp > 1e9 ? 'Miliar USD' :
                    data[0]?.gdp > 1e6 ? 'Juta USD' : 'USD';

    // Inflation data
    const inflationData = data.map(d => d.inflation_rate || 0);

    barChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: `GDP (${gdpUnit})`,
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
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: `GDP (${gdpUnit})`,
                        color: '#4FD1C5'
                    },
                    grid: { color: 'rgba(143, 160, 189, 0.1)' },
                    ticks: { color: '#4FD1C5' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Inflasi (%)',
                        color: '#E3A038'
                    },
                    grid: { drawOnChartArea: false },
                    ticks: { color: '#E3A038' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#8FA0BD' }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#EDEFF4',
                        font: { family: "'Inter', sans-serif", size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 41, 0.95)',
                    titleColor: '#E3A038',
                    bodyColor: '#EDEFF4',
                    borderColor: 'rgba(30, 44, 70, 1)',
                    borderWidth: 1
                }
            }
        }
    });
}

// ------------------------------------------------------------------
// Helper: Color generator untuk charts
// ------------------------------------------------------------------
function getColorForIndex(index, alpha = 1) {
    const colors = [
        `rgba(227, 160, 56, ${alpha})`, // Amber
        `rgba(79, 209, 197, ${alpha})`, // Cyan
        `rgba(214, 72, 63, ${alpha})`,  // Red
        `rgba(63, 167, 114, ${alpha})`, // Green
        `rgba(143, 160, 189, ${alpha})`,// Mist
    ];
    return colors[index % colors.length];
}

// ------------------------------------------------------------------
// UI State Management
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
    if (errorEl) {
        errorEl.classList.add('d-none');
    }
}

// ------------------------------------------------------------------
// Initialize on DOM ready
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    loadAllCountries();
});
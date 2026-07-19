/**
 * currency-trend.js — Currency Impact Dashboard
 * Menampilkan daftar kurs terkini (basis USD) dan grafik tren histori
 * pakai Chart.js. Klik salah satu mata uang di tabel untuk lihat trennya.
 */

const API_BASE = '/api';
let currencyChart = null;
let allRates = [];
let activeTargetCode = null;

async function loadRatesTable() {
    const tbody = document.getElementById('tw-rate-table-body');

    try {
        const res = await fetch(`${API_BASE}/currency?base=USD`);
        const json = await res.json();
        allRates = json.data || [];

        renderRatesTable(allRates);

        // Otomatis tampilkan grafik untuk mata uang pertama di daftar
        if (allRates.length > 0) {
            loadChartFor(allRates[0].target_currency);
        }
    } catch (err) {
        console.error('loadRatesTable error:', err);
        tbody.innerHTML = '<tr><td colspan="2" class="tw-muted">Gagal memuat kurs. Pastikan sudah jalankan fetch:exchange-rates.</td></tr>';
    }
}

function renderRatesTable(rates) {
    const tbody = document.getElementById('tw-rate-table-body');

    if (rates.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" class="tw-muted">Belum ada data kurs.</td></tr>';
        return;
    }

    tbody.innerHTML = rates.map((rate) => `
        <tr class="tw-rate-row ${rate.target_currency === activeTargetCode ? 'active' : ''}" data-target="${rate.target_currency}">
            <td class="font-mono">${rate.target_currency}</td>
            <td class="text-end font-mono">${parseFloat(rate.rate).toLocaleString('id-ID', { maximumFractionDigits: 4 })}</td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.tw-rate-row').forEach((row) => {
        row.addEventListener('click', () => loadChartFor(row.dataset.target));
    });
}

async function loadChartFor(targetCode) {
    activeTargetCode = targetCode;
    document.getElementById('tw-chart-pair-label').textContent = `USD → ${targetCode}`;
    renderRatesTable(allRates); // refresh biar highlight baris aktif ke-update

    try {
        const res = await fetch(`${API_BASE}/currency/history?base=USD&target=${targetCode}`);
        const json = await res.json();
        const history = json.data || [];

        const labels = history.map((h) => h.recorded_date);
        const values = history.map((h) => parseFloat(h.rate));

        renderChart(labels, values, targetCode);
    } catch (err) {
        console.error('loadChartFor error:', err);
    }
}

function renderChart(labels, values, targetCode) {
    const ctx = document.getElementById('tw-currency-chart');

    if (currencyChart) {
        currencyChart.destroy();
    }

    if (labels.length < 2) {
        // Data historis belum cukup untuk grafik garis yang bermakna.
        // Tetap tampilkan chart kosong dengan pesan, bukan chart error.
        currencyChart = new Chart(ctx, {
            type: 'line',
            data: { labels: labels.length ? labels : ['Hari ini'], datasets: [{
                label: `USD → ${targetCode}`,
                data: values.length ? values : [0],
                borderColor: '#4FD1C5',
                backgroundColor: 'rgba(79,209,197,0.1)',
                tension: 0.3,
                pointRadius: 3,
            }] },
            options: chartOptions('Data histori masih sedikit — jalankan fetch:exchange-rates beberapa hari berturut-turut untuk tren yang lebih jelas.'),
        });
        return;
    }

    currencyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: `USD → ${targetCode}`,
                data: values,
                borderColor: '#4FD1C5',
                backgroundColor: 'rgba(79,209,197,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 2,
            }],
        },
        options: chartOptions(),
    });
}

function chartOptions(subtitle = null) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            subtitle: subtitle ? {
                display: true,
                text: subtitle,
                color: '#8FA0BD',
                font: { size: 11 },
                padding: { bottom: 10 },
            } : undefined,
        },
        scales: {
            x: { ticks: { color: '#8FA0BD', font: { family: 'IBM Plex Mono', size: 10 } }, grid: { color: '#1E2C46' } },
            y: { ticks: { color: '#8FA0BD', font: { family: 'IBM Plex Mono', size: 10 } }, grid: { color: '#1E2C46' } },
        },
    };
}

document.addEventListener('DOMContentLoaded', () => {
    loadRatesTable();

    document.getElementById('tw-currency-search').addEventListener('input', (e) => {
        const query = e.target.value.toUpperCase();
        const filtered = allRates.filter((r) => r.target_currency.includes(query));
        renderRatesTable(filtered);
    });
});
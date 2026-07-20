@extends('layouts.app')

@section('title', 'Currency Impact Dashboard')

@push('styles')
    <style>
        #tw-currency-chart-wrap {
            height: 360px;
            position: relative;
        }

        .tw-rate-table th, .tw-rate-table td {
            border-color: var(--ink-750) !important;
            background-color: transparent !important;
            color: var(--paper) !important;
            font-size: 0.88rem;
            vertical-align: middle;
        }

        .tw-rate-table,
        .tw-rate-table > :not(caption) > * > * {
            background-color: transparent !important;
        }

        .tw-rate-table th {
            color: var(--mist) !important;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .tw-rate-row {
            cursor: pointer;
            transition: background 0.12s ease;
        }

        .tw-rate-row:hover { background: var(--ink-850); }
        .tw-rate-row.active { background: var(--ink-850); border-left: 2px solid var(--cyan-data); }
    </style>
@endpush

@section('content')

    <div class="container-fluid px-4 py-4">

        <div class="mb-4">
            <div class="tw-eyebrow">Fitur 4</div>
            <h2 class="font-display mb-0">Currency Impact Dashboard</h2>
            <p class="tw-muted mb-0" style="font-size:0.88rem;">Nilai tukar dan tren perubahan kurs terhadap basis USD.</p>
        </div>

        <div class="row g-4">

            <!-- Grafik tren -->
            <div class="col-lg-8">
                <div class="tw-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="tw-eyebrow mb-0">Tren Kurs — <span id="tw-chart-pair-label" class="font-mono">USD → —</span></div>
                    </div>
                    <div id="tw-currency-chart-wrap">
                        <canvas id="tw-currency-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Daftar kurs -->
            <div class="col-lg-4">
                <div class="tw-card">
                    <div class="tw-eyebrow">Kurs Terkini (Basis USD)</div>
                    <input type="text" id="tw-currency-search" class="form-control form-control-sm mb-3" placeholder="Cari kode mata uang...">
                    <div style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-borderless tw-rate-table mb-0">
                            <thead>
                                <tr><th>Mata Uang</th><th class="text-end">Kurs</th></tr>
                            </thead>
                            <tbody id="tw-rate-table-body">
                                <tr><td colspan="2" class="tw-muted">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/charts/currency-trend.js') }}"></script>
@endpush
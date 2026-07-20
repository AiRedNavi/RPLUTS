@extends('layouts.app')

@section('title', 'Country Comparison Engine')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">

            <!-- Header Halaman -->
            <div class="text-center mb-5">
                <div class="tw-eyebrow text-warning text-uppercase ls-1 mb-2">Supply Chain Risk Intelligence</div>
                <h1 class="font-display mb-3">Country Comparison Engine</h1>
                <p class="tw-muted mx-auto" style="max-width: 700px;">
                    Bandingkan hingga 4 negara sekaligus &mdash; analisis ekonomi, cuaca, kurs, dan skor risiko dalam satu panel terpusat.
                </p>
            </div>

            <!-- ============== PANEL UTAMA: Pilih Negara (Centered) ============== -->
            <div class="tw-card mb-5 shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4 align-items-end">

                        <!-- Kolom Input & List Negara Terpilih -->
                        <div class="col-md-8">
                            <label class="form-label tw-eyebrow mb-2">Pilih Negara untuk Dibandingkan</label>
                            <p class="tw-muted mb-3 small">Pilih 2–4 negara untuk melihat perbandingan mendalam.</p>

                            <!-- List Negara yang Sudah Dipilih (Chips) -->
                            <div id="selected-countries-list" class="d-flex flex-wrap gap-2 mb-3 min-h-40"></div>

                            <!-- Search Dropdown -->
                            <div class="dropdown w-100">
                                <input
                                    type="text"
                                    id="country-search-input"
                                    class="form-control form-control-lg bg-dark border-secondary text-light"
                                    placeholder="Ketik nama negara (contoh: Indonesia)..."
                                    autocomplete="off"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                <ul id="country-dropdown-menu" class="dropdown-menu dropdown-menu-dark w-100 shadow-lg" style="max-height: 300px; overflow-y: auto; z-index: 1050;"></ul>
                            </div>

                            <div id="comparison-error" class="alert alert-danger mt-3 d-none small" role="alert"></div>
                        </div>

                        <!-- Kolom Aksi & Filter Cepat -->
                        <div class="col-md-4 border-start-md ps-md-4">
                            <button id="compare-btn" class="btn btn-warning btn-lg w-100 fw-bold mb-3" type="button" disabled>
                                Bandingkan Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============== KONTEN HASIL PERBANDINGAN ============== -->

            <!-- Empty State -->
            <div id="comparison-empty-state" class="text-center py-5 my-5">
                <div class="mb-3 opacity-25">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-layers-half" viewBox="0 0 16 16">
                        <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8l-2.923 1.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882l-7.5-4Z"/>
                    </svg>
                </div>
                <h3 class="h4 fw-bold mb-2">Belum ada perbandingan aktif</h3>
                <p class="tw-muted mb-0">Silakan pilih negara di panel atas untuk memulai analisis komparatif.</p>
            </div>

            <!-- Loading State -->
            <div id="comparison-loading" class="text-center py-5 my-5 d-none">
                <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                <div class="tw-eyebrow mb-2 text-warning">Menghitung perbandingan...</div>
                <p class="tw-muted small">Mengambil data real-time dari sumber ekonomi dan meteorologi global.</p>
            </div>

            <!-- Results Container -->
            <div id="comparison-results" class="d-none animate-fade-in">

                <!-- Header Hasil -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom border-secondary">
                    <div>
                        <div class="tw-eyebrow text-warning">Hasil Analisis</div>
                        <h2 class="font-display h3 mb-0" id="comparison-title">Perbandingan Negara</h2>
                    </div>
                    <button id="clear-comparison-btn" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-2">
                        <span>&times;</span> Reset Data
                    </button>
                </div>

                <!-- Tabel Perbandingan Detail -->
                <div class="tw-card mb-4 shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-transparent border-bottom border-secondary py-3">
                        <div class="tw-eyebrow mb-0">Tabel Perbandingan Detail</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0 align-middle" id="comparison-table" style="min-width: 800px;">
                                <thead class="bg-darker">
                                    <tr id="comparison-table-head" class="text-muted small text-uppercase"></tr>
                                </thead>
                                <tbody id="comparison-table-body" class="border-0"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <!-- Risk Profile Radar Chart -->
                    <div class="col-lg-6">
                        <div class="tw-card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-bottom border-secondary py-3">
                                <div class="tw-eyebrow mb-0">Risk Profile Radar</div>
                            </div>
                            <div class="card-body">
                                <div class="rc-chart-wrap position-relative" style="height: 320px;">
                                    <canvas id="comparison-radar-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Economic Indicators Bar Chart -->
                    <div class="col-lg-6">
                        <div class="tw-card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-bottom border-secondary py-3">
                                <div class="tw-eyebrow mb-0">Indikator Ekonomi (GDP & Inflasi)</div>
                            </div>
                            <div class="card-body">
                                <div class="rc-chart-wrap position-relative" style="height: 320px;">
                                    <canvas id="comparison-bar-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Helper untuk animasi fade in */
    .animate-fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .min-h-40 {
        min-height: 40px;
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.2rem;
    }
    .bg-darker {
        background-color: #ded5d5;
    }
</style>
@endsection

@push('scripts')
<script src="{{ asset('js/comparison.js') }}"></script>
@endpush
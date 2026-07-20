@extends('layouts.app')

@section('title', 'Country Comparison Engine')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row g-4">

        <!-- ============== PANEL KIRI: Pilih Negara ============== -->
        <div class="col-lg-3">

            <!-- Control Panel: Pilih hingga 4 negara -->
            <div class="tw-card mb-4">
                <div class="tw-eyebrow">Pilih Negara untuk Dibandingkan</div>
                <p class="tw-muted mb-3" style="font-size:0.82rem;">Pilih 2–4 negara untuk melihat perbandingan ekonomi, cuaca, dan skor risiko.</p>

                <div id="selected-countries-list" class="mb-3"></div>

                <div class="dropdown">
                    <input
                        type="text"
                        id="country-search-input"
                        class="form-control mb-2"
                        placeholder="Cari negara..."
                        autocomplete="off"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                    <ul id="country-dropdown-menu" class="dropdown-menu dropdown-menu-dark w-100" style="max-height: 280px; overflow-y: auto;"></ul>
                </div>

                <button id="compare-btn" class="btn btn-outline-light w-100 mt-2" type="button" disabled>
                    Bandingkan Sekarang
                </button>

                <div id="comparison-error" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>

        <!-- ============== KONTEN UTAMA: Hasil Perbandingan ============== -->
        <div class="col-lg-9">

            <!-- Empty State -->
            <div id="comparison-empty-state" class="tw-card text-center py-5">
                <div class="tw-eyebrow mb-2">Belum ada perbandingan</div>
                <h3 class="mb-2">Pilih 2–4 negara untuk memulai</h3>
                <p class="tw-muted mb-0">Gunakan panel di sebelah kiri untuk memilih negara yang ingin dibandingkan. Anda akan melihat perbandingan GDP, inflasi, risiko, cuaca, dan kurs dalam satu tampilan.</p>
            </div>

            <!-- Loading State -->
            <div id="comparison-loading" class="tw-card text-center py-5 d-none">
                <div class="spinner-border text-warning mb-3" role="status"></div>
                <div class="tw-eyebrow mb-2">Menghitung perbandingan...</div>
                <p class="tw-muted mb-0">Mengambil data ekonomi, cuaca, dan skor risiko dari berbagai sumber.</p>
            </div>

            <!-- Results -->
            <div id="comparison-results" class="d-none">

                <!-- Header hasil -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <div class="tw-eyebrow">Hasil Perbandingan</div>
                        <h2 class="font-display mb-0" id="comparison-title">—</h2>
                    </div>
                    <button id="clear-comparison-btn" class="btn btn-sm btn-outline-danger" type="button">
                        Reset Perbandingan
                    </button>
                </div>

                <!-- Tabel perbandingan detail -->
                <div class="tw-card mb-4">
                    <div class="tw-eyebrow">Perbandingan Detail</div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0" id="comparison-table">
                            <thead>
                                <tr id="comparison-table-head"></tr>
                            </thead>
                            <tbody id="comparison-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Risk Profile Radar Chart -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="tw-card h-100">
                            <div class="tw-eyebrow">Risk Profile Comparison</div>
                            <div class="rc-chart-wrap" style="position: relative; height: 300px;">
                                <canvas id="comparison-radar-chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Economic Indicators Bar Chart -->
                    <div class="col-md-6">
                        <div class="tw-card h-100">
                            <div class="tw-eyebrow">GDP & Inflasi</div>
                            <div class="rc-chart-wrap" style="position: relative; height: 300px;">
                                <canvas id="comparison-bar-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/comparison.js') }}"></script>
@endpush
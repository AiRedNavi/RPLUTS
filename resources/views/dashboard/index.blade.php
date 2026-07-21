@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <!-- ============== HERO: Split-flap ticker negara risiko tertinggi ============== -->
    <div class="tw-ticker">
        <div class="container-fluid px-4 d-flex align-items-center gap-4">
            <span class="tw-ticker-label">⚠ Risiko Tertinggi</span>
            <div class="flex-grow-1 overflow-hidden">
                <div class="tw-flap-track" id="tw-ticker-track">
                    <span class="tw-muted font-mono" style="font-size:0.85rem;">Memuat data risiko...</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <div class="row g-4">

            <!-- ============== CONTROL PANEL (kiri) ============== -->
            <div class="col-lg-3">
                <div class="tw-card mb-4">
                    <div class="tw-eyebrow">Pilih Negara</div>
                    <input
                        type="text"
                        id="tw-country-search"
                        class="form-control mb-2"
                        placeholder="Cari negara... (mis. Indonesia)"
                        autocomplete="off"
                    >
                    <div id="tw-country-results" class="list-group" style="max-height: 260px; overflow-y: auto;"></div>
                </div>

                <div class="tw-card mb-4">
                    <div class="tw-eyebrow">Filter Region</div>
                    <div class="d-flex flex-wrap gap-2" id="tw-region-filters">
                        <button class="btn btn-sm btn-outline-light" data-region="">Semua</button>
                        <button class="btn btn-sm btn-outline-light" data-region="Asia">Asia</button>
                        <button class="btn btn-sm btn-outline-light" data-region="Europe">Europe</button>
                        <button class="btn btn-sm btn-outline-light" data-region="Africa">Africa</button>
                        <button class="btn btn-sm btn-outline-light" data-region="Americas">Americas</button>
                        <button class="btn btn-sm btn-outline-light" data-region="Oceania">Oceania</button>
                    </div>
                </div>

                <div class="tw-card">
                    <div class="tw-eyebrow">Navigasi Cepat</div>
                    <a href="{{ url('/weather') }}" class="tw-quicklink">🌩 Peta Cuaca Global</a>
                    <a href="{{ url('/currency') }}" class="tw-quicklink">💱 Dampak Kurs Mata Uang</a>
                    <a href="{{ url('/news') }}" class="tw-quicklink">📰 Intelijen Berita</a>
                    <a href="{{ url('/ports') }}" class="tw-quicklink">⚓ Lokasi Pelabuhan</a>
                    <a href="{{ url('/comparison') }}" class="tw-quicklink">⇄ Bandingkan Negara</a>
                </div>
            </div>

            <!-- ============== KONTEN UTAMA (kanan) ============== -->
            <div class="col-lg-9">

                <div id="tw-empty-state" class="tw-card text-center py-5">
                    <div class="tw-eyebrow mb-2">Belum ada negara dipilih</div>
                    <h3 class="mb-2">Cari atau pilih negara di panel sebelah kiri</h3>
                    <p class="tw-muted mb-0">Ringkasan risiko, cuaca, indikator ekonomi, dan berita terbaru akan tampil di sini.</p>
                </div>

                <div id="tw-country-detail" class="d-none">

                    <!-- Header negara terpilih -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <div class="tw-eyebrow" id="tw-detail-iso">—</div>
                            <h2 class="font-display mb-0" id="tw-detail-name">—</h2>
                        </div>
                       <div class="d-flex align-items-center gap-2">
                            <span class="tw-badge" id="tw-detail-risk-badge">—</span>
                            <button id="tw-btn-add-watchlist" class="btn btn-sm btn-outline-light" data-country-id="">
                                + Watchlist
                            </button>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Risk breakdown -->
                        <div class="col-md-6">
                            <div class="tw-card h-100" id="tw-risk-card">
                                <div class="tw-eyebrow">Risk Scoring Engine</div>
                                <div class="d-flex align-items-baseline gap-2 mb-3">
                                    <span class="font-mono fw-semibold" style="font-size: 2.2rem;" id="tw-total-score">—</span>
                                    <span class="tw-muted">/ 100</span>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between tw-muted" style="font-size:0.82rem;">
                                        <span>Cuaca</span><span class="font-mono" id="tw-score-weather">—</span>
                                    </div>
                                    <div class="progress" style="height:5px; background: var(--ink-750);">
                                        <div class="progress-bar" id="tw-bar-weather" style="background: var(--signal-amber); width:0%"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between tw-muted" style="font-size:0.82rem;">
                                        <span>Inflasi</span><span class="font-mono" id="tw-score-inflation">—</span>
                                    </div>
                                    <div class="progress" style="height:5px; background: var(--ink-750);">
                                        <div class="progress-bar" id="tw-bar-inflation" style="background: var(--signal-amber); width:0%"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between tw-muted" style="font-size:0.82rem;">
                                        <span>Sentimen Berita</span><span class="font-mono" id="tw-score-news">—</span>
                                    </div>
                                    <div class="progress" style="height:5px; background: var(--ink-750);">
                                        <div class="progress-bar" id="tw-bar-news" style="background: var(--signal-amber); width:0%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between tw-muted" style="font-size:0.82rem;">
                                        <span>Volatilitas Kurs</span><span class="font-mono" id="tw-score-currency">—</span>
                                    </div>
                                    <div class="progress" style="height:5px; background: var(--ink-750);">
                                        <div class="progress-bar" id="tw-bar-currency" style="background: var(--signal-amber); width:0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cuaca & indikator ekonomi -->
                        <div class="col-md-6">
                            <div class="tw-card h-100">
                                <div class="tw-eyebrow">Kondisi Saat Ini</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div class="tw-muted" style="font-size:0.78rem;">Suhu</div>
                                        <div class="font-mono fs-5" id="tw-weather-temp">—</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="tw-muted" style="font-size:0.78rem;">Risiko Badai</div>
                                        <div id="tw-weather-storm">—</div>
                                    </div>
                                </div>
                                <hr class="tw-divider">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="tw-muted" style="font-size:0.78rem;">GDP</div>
                                        <div class="font-mono" id="tw-econ-gdp">—</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="tw-muted" style="font-size:0.78rem;">Inflasi</div>
                                        <div class="font-mono" id="tw-econ-inflation">—</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="tw-muted" style="font-size:0.78rem;">Populasi</div>
                                        <div class="font-mono" id="tw-econ-population">—</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="tw-muted" style="font-size:0.78rem;">Mata Uang</div>
                                        <div class="font-mono" id="tw-econ-currency">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Berita terbaru -->
                    <div class="tw-card">
                        <div class="tw-eyebrow">Berita Terkait Negara Ini</div>
                        <div id="tw-news-list">
                            <p class="tw-muted mb-0" style="font-size:0.9rem;">Memuat berita...</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
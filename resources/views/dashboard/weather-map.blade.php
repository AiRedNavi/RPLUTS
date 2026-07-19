@extends('layouts.app')

@section('title', 'Global Weather Monitoring')

@push('styles')
    <style>
        #tw-weather-map {
            height: calc(100vh - 220px);
            min-height: 480px;
            border-radius: 6px;
            border: 1px solid var(--ink-750);
            background: var(--ink-900);
        }

        /* Override tampilan default Leaflet supaya nyambung sama tema gelap */
        .leaflet-popup-content-wrapper {
            background: var(--ink-900);
            color: var(--paper);
            border: 1px solid var(--ink-750);
            border-radius: 6px;
        }
        .leaflet-popup-tip { background: var(--ink-900); }
        .leaflet-popup-content { font-family: var(--font-body); font-size: 0.85rem; margin: 0.75rem 1rem; }
        .leaflet-container { background: var(--ink-950); }
        .leaflet-control-zoom a {
            background: var(--ink-900) !important;
            color: var(--paper) !important;
            border-color: var(--ink-750) !important;
        }

        .tw-storm-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
@endpush

@section('content')

    <div class="container-fluid px-4 py-4">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <div class="tw-eyebrow">Fitur 3</div>
                <h2 class="font-display mb-0">Global Weather Monitoring</h2>
                <p class="tw-muted mb-0" style="font-size:0.88rem;">Sebaran suhu, curah hujan, dan risiko badai per negara.</p>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <span class="tw-eyebrow mb-0">Legenda</span>
                <span class="d-flex align-items-center gap-1" style="font-size:0.8rem;">
                    <span class="tw-storm-dot" style="background: var(--signal-green);"></span> Rendah
                </span>
                <span class="d-flex align-items-center gap-1" style="font-size:0.8rem;">
                    <span class="tw-storm-dot" style="background: var(--signal-amber);"></span> Sedang
                </span>
                <span class="d-flex align-items-center gap-1" style="font-size:0.8rem;">
                    <span class="tw-storm-dot" style="background: var(--signal-red);"></span> Tinggi
                </span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-9">
                <div id="tw-weather-map"></div>
            </div>

            <div class="col-lg-3">
                <div class="tw-card">
                    <div class="tw-eyebrow">Negara Berisiko Badai Tinggi</div>
                    <div id="tw-storm-list" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                        <p class="tw-muted mb-0" style="font-size:0.85rem;">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/maps/weather-map.js') }}"></script>
@endpush
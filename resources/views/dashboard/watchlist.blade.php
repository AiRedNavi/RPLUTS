@extends('layouts.app')

@section('title', 'Watchlist Saya')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="tw-eyebrow">Personal Monitoring</div>
            <h2 class="font-display mb-0">Watchlist Saya</h2>
        </div>
    </div>

    <div id="tw-watchlist-empty" class="tw-card text-center py-5 d-none">
        <div class="tw-eyebrow mb-2">Watchlist kosong</div>
        <h4 class="mb-2">Belum ada negara yang dipantau</h4>
        <p class="tw-muted mb-3">Tambahkan negara dari dashboard utama untuk mulai memantau risikonya di sini.</p>
        <a href="{{ url('/') }}" class="btn btn-outline-light btn-sm">Ke Dashboard</a>
    </div>

    <div id="tw-watchlist-grid" class="row g-3"></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/watchlist.js') }}"></script>
@endpush
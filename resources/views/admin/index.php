{{-- resources/views/admin/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Overview — AstralMonitor')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <div class="tw-eyebrow">Admin</div>
            <h2 class="font-display mb-0">Control Room</h2>
        </div>
        @include('admin.partials.nav')
    </div>

    @if (session('success'))
        <div class="tw-badge tw-badge--low d-block mb-3 p-2" style="font-size:0.85rem;">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                <div class="tw-card h-100">
                    <div class="tw-eyebrow mb-1">Total User</div>
                    <div class="font-mono fs-3">{{ $stats['total_users'] }}</div>
                    <div class="tw-muted" style="font-size:0.78rem;">{{ $stats['total_admins'] }} admin</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('admin.ports.index') }}" class="text-decoration-none">
                <div class="tw-card h-100">
                    <div class="tw-eyebrow mb-1">Total Pelabuhan</div>
                    <div class="font-mono fs-3" style="color: var(--signal-amber);">{{ $stats['total_ports'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('admin.articles.index') }}" class="text-decoration-none">
                <div class="tw-card h-100">
                    <div class="tw-eyebrow mb-1">Total Artikel</div>
                    <div class="font-mono fs-3" style="color: var(--signal-green);">{{ $stats['total_articles'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('admin.articles.index') }}" class="text-decoration-none">
                <div class="tw-card h-100 d-flex flex-column justify-content-center align-items-center text-center">
                    <div class="tw-eyebrow mb-1">Sinkronisasi Berita</div>
                    <div style="font-size:1.4rem;">🔄</div>
                    <div class="tw-muted" style="font-size:0.75rem;">Fetch & analisis di halaman Articles</div>
                </div>
            </a>
        </div>
    </div>

    <div class="tw-card">
        <div class="tw-eyebrow mb-3">Artikel Terbaru</div>
        @forelse ($latestArticles as $article)
            <div class="py-2 border-bottom tw-divider d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-medium">{{ $article->title }}</div>
                    <div class="tw-muted" style="font-size:0.78rem;">
                        {{ $article->author->name ?? '—' }} · {{ $article->country->name ?? 'Global' }}
                    </div>
                </div>
                <div class="font-mono tw-muted" style="font-size:0.78rem;">
                    {{ $article->published_at?->format('d M Y') ?? '—' }}
                </div>
            </div>
        @empty
            <p class="tw-muted mb-0" style="font-size:0.9rem;">Belum ada artikel dipublikasikan.</p>
        @endforelse
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Kelola Artikel — Admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <div class="tw-eyebrow">Admin / Article Management</div>
            <h2 class="font-display mb-0">Kelola Artikel Analisis</h2>
        </div>
        @include('admin.partials.nav')
    </div>

    <div class="tw-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="tw-eyebrow">Sinkronisasi Otomatis</div>
                <p class="tw-muted mb-0" style="font-size:0.85rem;">
                    Ambil berita terbaru dari GNews lalu jalankan analisis sentimen — tanpa perlu terminal.
                </p>
            </div>
            <button type="button" id="tw-btn-sync-news" class="btn btn-sm btn-outline-light">
                Fetch & Analisis Berita
            </button>
        </div>
        <div id="tw-sync-status" class="mt-3 d-none">
            <pre id="tw-sync-log" class="font-mono p-2 mb-0" style="background: var(--ink-950); color: var(--mist); font-size:0.78rem; max-height:200px; overflow-y:auto; white-space:pre-wrap;"></pre>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="tw-card">
                <div class="tw-eyebrow mb-1">Total Artikel</div>
                <div class="font-mono fs-3">{{ $stats['total_articles'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tw-card">
                <div class="tw-eyebrow mb-1">Bulan Ini</div>
                <div class="font-mono fs-3" style="color: var(--signal-green);">{{ $stats['published_this_month'] }}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="tw-badge tw-badge--low d-block mb-3 p-2" style="font-size:0.85rem;">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="tw-badge tw-badge--high d-block mb-3 p-2" style="font-size:0.85rem;">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 flex-wrap">
        <form method="GET" style="max-width: 340px;">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul artikel...">
        </form>
        <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalCreateArticle">
            + Tulis Artikel
        </button>
    </div>

    <div class="tw-card p-0">
        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Negara Terkait</th>
                    <th>Dipublikasi</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $article)
                    <tr>
                        <td>{{ $article->title }}</td>
                        <td>{{ $article->author->name ?? '—' }}</td>
                        <td>{{ $article->country->name ?? 'Global' }}</td>
                        <td class="font-mono" style="font-size:0.8rem;">{{ $article->published_at?->format('d M Y') ?? '—' }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditArticle{{ $article->id }}">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus artikel \'{{ $article->title }}\'?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEditArticle{{ $article->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content" style="background: var(--ink-900); color: var(--paper);">
                                <form method="POST" action="{{ route('admin.articles.update', $article) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header" style="border-color: var(--ink-750);">
                                        <h5 class="modal-title">Edit Artikel</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Judul</label>
                                            <input type="text" name="title" value="{{ $article->title }}" class="form-control" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Konten</label>
                                            <textarea name="content" rows="6" class="form-control" required>{{ $article->content }}</textarea>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Negara Terkait (opsional)</label>
                                                <select name="related_country_id" class="form-select">
                                                    <option value="">Global (tidak spesifik)</option>
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country->id }}" @selected($country->id === $article->related_country_id)>
                                                            {{ $country->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tanggal Publikasi</label>
                                                <input type="datetime-local" name="published_at" class="form-control"
                                                       value="{{ $article->published_at?->format('Y-m-d\TH:i') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer" style="border-color: var(--ink-750);">
                                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-outline-warning">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="5" class="text-center tw-muted py-4">Belum ada artikel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $articles->links() }}</div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modalCreateArticle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: var(--ink-900); color: var(--paper);">
            <form method="POST" action="{{ route('admin.articles.store') }}">
                @csrf
                <div class="modal-header" style="border-color: var(--ink-750);">
                    <h5 class="modal-title">Tulis Artikel Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Judul</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Konten</label>
                        <textarea name="content" rows="6" class="form-control" required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Negara Terkait (opsional)</label>
                            <select name="related_country_id" class="form-select">
                                <option value="">Global (tidak spesifik)</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Publikasi (kosongkan = sekarang)</label>
                            <input type="datetime-local" name="published_at" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-color: var(--ink-750);">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-outline-light">Publikasikan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('tw-btn-sync-news')?.addEventListener('click', async function () {
    const btn = this;
    const statusBox = document.getElementById('tw-sync-status');
    const logBox = document.getElementById('tw-sync-log');

    btn.disabled = true;
    btn.textContent = '⏳ Memproses...';
    statusBox.classList.remove('d-none');
    logBox.textContent = 'Menghubungi server, mohon tunggu (bisa memakan waktu 30–60 detik)...';

    try {
        const res = await fetch('{{ route('admin.news.sync') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
        });

        const json = await res.json();
        logBox.textContent = (json.log || []).join('\n\n');

        if (json.success) {
            btn.textContent = '✅ Berhasil, muat ulang...';
            setTimeout(() => window.location.reload(), 1200);
        } else {
            btn.textContent = '🔄 Fetch & Analisis Berita';
            btn.disabled = false;
            alert(json.message || 'Gagal menjalankan sinkronisasi.');
        }
    } catch (err) {
        logBox.textContent = 'Terjadi kesalahan jaringan: ' + err.message;
        btn.textContent = '🔄 Fetch & Analisis Berita';
        btn.disabled = false;
    }
});
</script>
@endpush
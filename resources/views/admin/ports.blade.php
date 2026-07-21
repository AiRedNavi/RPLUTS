{{-- resources/views/admin/ports.blade.php --}}
@extends('layouts.app')

@section('title', 'Kelola Pelabuhan — Admin')

@section('content')
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <div class="tw-eyebrow">Admin / User Management</div>
                <h2 class="font-display mb-0">Kelola User</h2>
            </div>
            @include('admin.partials.nav')
        </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="tw-card">
                <div class="tw-eyebrow mb-1">Total Pelabuhan</div>
                <div class="font-mono fs-3">{{ $stats['total_ports'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tw-card">
                <div class="tw-eyebrow mb-1">Negara Tercakup</div>
                <div class="font-mono fs-3" style="color: var(--signal-green);">{{ $stats['total_countries_covered'] }}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="tw-badge tw-badge--low d-block mb-3 p-2" style="font-size:0.85rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="tw-badge tw-badge--high d-block mb-3 p-2" style="font-size:0.85rem;">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="tw-badge tw-badge--high d-block mb-3 p-2" style="font-size:0.85rem;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 flex-wrap">
        <form method="GET" style="max-width: 340px;">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau UN/LOCODE...">
        </form>
        <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalCreatePort">
            + Tambah Pelabuhan
        </button>
    </div>

    <div class="tw-card p-0">
        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Negara</th>
                    <th>UN/LOCODE</th>
                    <th>Koordinat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ports as $port)
                    <tr>
                        <td>{{ $port->name }}</td>
                        <td>{{ $port->country->name ?? '—' }}</td>
                        <td class="font-mono">{{ $port->unlocode ?? '—' }}</td>
                        <td class="font-mono tw-muted" style="font-size:0.8rem;">{{ $port->latitude }}, {{ $port->longitude }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditPort{{ $port->id }}">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.ports.destroy', $port) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus pelabuhan {{ $port->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEditPort{{ $port->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content" style="background: var(--ink-900); color: var(--paper);">
                                <form method="POST" action="{{ route('admin.ports.update', $port) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header" style="border-color: var(--ink-750);">
                                        <h5 class="modal-title">Edit Pelabuhan</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Nama</label>
                                            <input type="text" name="name" value="{{ $port->name }}" class="form-control" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Negara</label>
                                            <select name="country_id" class="form-select" required>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}" @selected($country->id === $port->country_id)>
                                                        {{ $country->name }} ({{ $country->iso_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label">Latitude</label>
                                                <input type="number" step="any" name="latitude" value="{{ $port->latitude }}" class="form-control" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Longitude</label>
                                                <input type="number" step="any" name="longitude" value="{{ $port->longitude }}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">UN/LOCODE</label>
                                            <input type="text" name="unlocode" value="{{ $port->unlocode }}" class="form-control" maxlength="10">
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
                    <tr><td colspan="5" class="text-center tw-muted py-4">Belum ada pelabuhan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $ports->links() }}</div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modalCreatePort" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--ink-900); color: var(--paper);">
            <form method="POST" action="{{ route('admin.ports.store') }}">
                @csrf
                <div class="modal-header" style="border-color: var(--ink-750);">
                    <h5 class="modal-title">Tambah Pelabuhan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Negara</label>
                        <select name="country_id" class="form-select" required>
                            <option value="" disabled selected>Pilih negara</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->iso_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">UN/LOCODE</label>
                        <input type="text" name="unlocode" class="form-control" maxlength="10">
                    </div>
                </div>
                <div class="modal-footer" style="border-color: var(--ink-750);">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-outline-light">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
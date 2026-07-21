{{-- resources/views/admin/users.blade.php --}}
@extends('layouts.app')

@section('title', 'Kelola User — Admin')

@section('content')
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
                <div class="tw-eyebrow mb-1">Total User</div>
                <div class="font-mono fs-3">{{ $stats['total_users'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tw-card">
                <div class="tw-eyebrow mb-1">Total Admin</div>
                <div class="font-mono fs-3" style="color: var(--signal-amber);">{{ $stats['total_admins'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tw-card">
                <div class="tw-eyebrow mb-1">User Baru (7 hari)</div>
                <div class="font-mono fs-3" style="color: var(--signal-green);">{{ $stats['new_this_week'] }}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="tw-badge tw-badge--low d-block mb-3 p-2" style="font-size:0.85rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="tw-badge tw-badge--high d-block mb-3 p-2" style="font-size:0.85rem;">{{ session('error') }}</div>
    @endif

    <form method="GET" class="mb-3" style="max-width: 340px;">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau email...">
    </form>

    <div class="tw-card p-0">
        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Bergabung</th>
                    <th>Role</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td class="font-mono">{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            @if ($user->isAdmin())
                                <span class="tw-badge tw-badge--medium">Admin</span>
                            @else
                                <span class="tw-badge" style="background: rgba(143,160,189,0.15); color: var(--mist);">User</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-role', $user) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        {{ $user->isAdmin() ? 'Cabut Admin' : 'Jadikan Admin' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus user {{ $user->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            @else
                                <span class="tw-muted small">Akun kamu</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center tw-muted py-4">Belum ada user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
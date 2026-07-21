{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — TRADEWATCH')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: var(--color-bg-navy, #0b1324); color: #e8edf7; font-family: 'Inter', sans-serif; }
        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 220px; background: var(--color-bg-panel, #10192e);
            border-right: 1px solid var(--color-border-navy, #223050);
            padding: 1.5rem 1rem;
        }
        .admin-sidebar__brand { font-family: 'Space Grotesk', sans-serif; font-weight: 700; color: #fff; font-size: 1.1rem; margin-bottom: 1.75rem; display: block; }
        .admin-sidebar__nav a {
            display: block; padding: .55rem .75rem; border-radius: .4rem;
            color: #8ea0c2; text-decoration: none; font-size: .9rem; margin-bottom: .2rem;
        }
        .admin-sidebar__nav a:hover, .admin-sidebar__nav a.active {
            background: rgba(245,166,35,.12); color: var(--color-signal-amber, #f5a623);
        }
        .admin-main { flex: 1; }
        .admin-eyebrow { font-family: 'IBM Plex Mono', monospace; font-size: .7rem; letter-spacing: .12em; color: var(--color-signal-amber, #f5a623); text-transform: uppercase; }
        .admin-title { font-family: 'Space Grotesk', sans-serif; color: #fff; font-size: 1.5rem; margin-top: .25rem; }
        .admin-panel { background: var(--color-bg-panel, #10192e); border: 1px solid var(--color-border-navy, #223050); border-radius: .5rem; }
        .admin-panel table { --bs-table-bg: transparent; margin: 0; }
        .font-mono { font-family: 'IBM Plex Mono', monospace; }
        .badge-signal { padding: .3rem .6rem; border-radius: .35rem; font-size: .75rem; font-weight: 600; }
        .badge-signal--amber { background: rgba(245,166,35,.18); color: var(--color-signal-amber, #f5a623); }
        .badge-signal--muted { background: rgba(255,255,255,.08); color: #8ea0c2; }
        .alert-signal--green { background: rgba(46,204,113,.15); border: 1px solid #2ecc71; color: #b7f0cc; border-radius: .5rem; padding: .75rem 1rem; }
        .alert-signal--red { background: rgba(220,53,69,.15); border: 1px solid #dc3545; color: #ffb3ba; border-radius: .5rem; padding: .75rem 1rem; }
        .control-input { background: #0b1324; border: 1px solid #223050; color: #fff; }
        .control-input:focus { background: #0b1324; color: #fff; border-color: var(--color-signal-amber, #f5a623); box-shadow: none; }
    </style>
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a href="{{ route('admin.users.index') }}" class="admin-sidebar__brand">TRADEWATCH<br><small style="font-size:.65rem; color:#8ea0c2; font-weight:400;">Admin Panel</small></a>
            <nav class="admin-sidebar__nav">
                {{-- Users --}}
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">👤 Users</a>

                {{-- Ports --}}
                <a href="{{ route('admin.ports.index') }}" class="{{ request()->routeIs('admin.ports.*') ? 'active' : '' }}">⚓ Ports</a>

                {{-- Articles --}}
                <a href="{{ route('admin.articles.index') }}" class="{{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">📄 Articles</a>

                {{-- Risk Weights --}}
                <a href="{{ route('admin.risk-weights.index') }}" class="{{ request()->routeIs('admin.risk-weights.*') ? 'active' : '' }}">⚖️ Risk Weights</a>
            </nav>
            <hr style="border-color:#223050;">
            <a href="{{ route('dashboard.index') }}" class="d-block small text-secondary text-decoration-none mb-2">← Kembali ke Dashboard</a>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Logout</button>
            </form>
        </aside>
        <main class="admin-main p-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
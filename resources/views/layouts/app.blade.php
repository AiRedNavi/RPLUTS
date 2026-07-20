<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · TRADEWATCH</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================
           DESIGN TOKENS — Nuansa ruang kontrol pelabuhan / instrumen
           monitoring maritim. Warna sinyal (amber/merah/hijau) diambil
           dari makna asli bendera semaphore & lampu sinyal pelabuhan,
           bukan sekadar aksen dekoratif.
           ========================================================== */
        :root {
            --ink-950: #0A111F;
            --ink-900: #0F1729;
            --ink-850: #131D31;
            --ink-750: #1E2C46;
            --ink-700: #29395A;

            --paper: #EDEFF4;
            --mist: #8FA0BD;
            --mist-dim: #5D6C87;

            --signal-amber: #E3A038;
            --signal-red: #D6483F;
            --signal-green: #3FA772;
            --cyan-data: #4FD1C5;

            --font-display: 'Space Grotesk', sans-serif;
            --font-body: 'Inter', sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
        }

        html, body {
            background: var(--ink-950);
            color: var(--paper);
            font-family: var(--font-body);
            min-height: 100vh;
        }

        h1, h2, h3, h4, .font-display {
            font-family: var(--font-display);
            letter-spacing: -0.01em;
        }

        .font-mono { font-family: var(--font-mono); }

        a { color: var(--cyan-data); text-decoration: none; }
        a:hover { color: #7EE8DD; }

        /* -------------------- Navbar -------------------- */
        .tw-navbar {
            background: var(--ink-900);
            border-bottom: 1px solid var(--ink-750);
            padding: 0.85rem 0;
        }

        .tw-brand {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.04em;
            color: var(--paper);
        }

        .tw-brand span { color: var(--signal-amber); }

        .tw-nav-link {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--mist);
            padding: 0.4rem 0.85rem;
            border-radius: 4px;
            transition: color 0.15s ease, background 0.15s ease;
        }

        .tw-nav-link:hover,
        .tw-nav-link.active {
            color: var(--paper);
            background: var(--ink-850);
        }

        .tw-clock {
            font-family: var(--font-mono);
            font-size: 0.78rem;
            color: var(--mist-dim);
            letter-spacing: 0.03em;
        }

        /* -------------------- Cards -------------------- */
        .tw-card {
            background: var(--ink-900);
            border: 1px solid var(--ink-750);
            border-radius: 6px;
            padding: 1.25rem;
        }

        .tw-eyebrow {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--mist-dim);
            margin-bottom: 0.5rem;
        }

        /* Garis aksen kiri kartu, warnanya berubah sesuai risk level —
           meniru bendera sinyal pelabuhan (amber/merah/hijau). */
        .tw-card--risk-low { border-left: 3px solid var(--signal-green); }
        .tw-card--risk-medium { border-left: 3px solid var(--signal-amber); }
        .tw-card--risk-high { border-left: 3px solid var(--signal-red); }

        .tw-badge {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.2rem 0.55rem;
            border-radius: 3px;
            display: inline-block;
        }

        .tw-badge--low { background: rgba(63,167,114,0.15); color: var(--signal-green); }
        .tw-badge--medium { background: rgba(227,160,56,0.15); color: var(--signal-amber); }
        .tw-badge--high { background: rgba(214,72,63,0.15); color: var(--signal-red); }

        .tw-badge--positive { background: rgba(63,167,114,0.15); color: var(--signal-green); }
        .tw-badge--neutral { background: rgba(143,160,189,0.15); color: var(--mist); }
        .tw-badge--negative { background: rgba(214,72,63,0.15); color: var(--signal-red); }

        /* -------------------- Split-flap ticker (signature element) --------
           Papan jadwal ala bandara/pelabuhan lama, menampilkan negara
           dengan risiko tertinggi secara bergantian. */
        .tw-ticker {
            background: var(--ink-900);
            border-top: 1px solid var(--ink-750);
            border-bottom: 1px solid var(--ink-750);
            padding: 0.9rem 0;
            overflow: hidden;
        }

        .tw-ticker-label {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--signal-red);
            white-space: nowrap;
        }

        .tw-flap-track {
            display: flex;
            gap: 2.5rem;
            white-space: nowrap;
        }

        .tw-flap-item {
            display: inline-flex;
            align-items: baseline;
            gap: 0.6rem;
            font-family: var(--font-mono);
        }

        .tw-flap-code {
            background: var(--ink-850);
            border: 1px solid var(--ink-750);
            border-radius: 3px;
            padding: 0.15rem 0.45rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            font-size: 0.85rem;
        }

        .tw-flap-score {
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* -------------------- Form controls -------------------- */
        .form-select, .form-control {
            background: var(--ink-850);
            border: 1px solid var(--ink-750);
            color: var(--paper);
        }

        .form-select:focus, .form-control:focus {
            background: var(--ink-850);
            color: var(--paper);
            border-color: var(--cyan-data);
            box-shadow: 0 0 0 0.2rem rgba(79,209,197,0.15);
        }

        .form-select option { background: var(--ink-850); }

        ::placeholder { color: var(--mist-dim); opacity: 1; }

        /* -------------------- Misc -------------------- */
        .tw-muted { color: var(--mist); }
        .tw-divider { border-color: var(--ink-750); }

        .tw-quicklink {
            display: block;
            padding: 0.6rem 0.75rem;
            border-radius: 5px;
            color: var(--mist);
            font-size: 0.88rem;
            font-weight: 500;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .tw-quicklink:hover {
            background: var(--ink-850);
            color: var(--paper);
        }

        /* Accessibility: keyboard focus tetap terlihat jelas di tema gelap */
        a:focus-visible, button:focus-visible, .form-select:focus-visible, .form-control:focus-visible {
            outline: 2px solid var(--cyan-data);
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: 0.001ms !important; animation-iteration-count: 1 !important; }
        }
    </style>

    @stack('styles')
</head>
<body>

    <nav class="tw-navbar">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-4">
                <a href="{{ url('/') }}" class="tw-brand">ASTRAL<span>MONITOR</span></a>
                <div class="d-none d-lg-flex gap-1">
                    <a href="{{ url('/') }}" class="tw-nav-link active">Dashboard</a>
                    <a href="{{ url('/weather') }}" class="tw-nav-link">Cuaca</a>
                    <a href="{{ url('/currency') }}" class="tw-nav-link">Kurensi</a>
                    <a href="{{ url('/news') }}" class="tw-nav-link">Berita</a>
                    <a href="{{ url('/ports') }}" class="tw-nav-link">Pelabuhan</a>
                    <a href="{{ url('/comparison') }}" class="tw-nav-link">Bandingkan</a>
                    <a href="{{ url('/watchlist') }}" class="tw-nav-link">Watchlist</a>
                </div>
            </div>
            <div class="tw-clock" id="tw-clock">--:--:-- UTC</div>
        </div>
    </nav>

    @yield('content')

    <footer class="border-top tw-divider py-4 mt-5">
        <div class="container-fluid px-4 tw-muted" style="font-size: 0.82rem;">
            Global Supply Chain Risk Intelligence Platform — data cuaca, ekonomi, kurs, dan berita
            diperbarui berkala dari sumber eksternal.
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <script>
        // Jam UTC kecil di navbar, kesan "monitoring room" — bukan cuma dekorasi,
        // membantu analis tahu freshness data yang mereka lihat.
        function twUpdateClock() {
            const el = document.getElementById('tw-clock');
            if (!el) return;
            const now = new Date();
            el.textContent = now.toISOString().slice(11, 19) + ' UTC';
        }
        twUpdateClock();
        setInterval(twUpdateClock, 1000);
    </script>

    @stack('scripts')
</body>
</html>
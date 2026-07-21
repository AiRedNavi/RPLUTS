# AMPHOREUS

**Global Supply Chain Risk Intelligence Platform**

Tugas akhir kuliah — platform monitoring risiko rantai pasok global yang mengumpulkan data cuaca, ekonomi, kurs mata uang, dan berita dari berbagai negara, lalu mengolahnya menjadi skor risiko yang bisa dipantau secara real-time.

> Nama lokal proyek: `rpluts` · Repository: `RPLUTS` · Local dev: `http://rpluts.test` (Laragon)

---

## Tentang Nama "Amphoreus"

Nama **Amphoreus** diambil dari salah satu planet dalam game *Honkai: Star Rail*. Dalam cerita game tersebut, Amphoreus pada akhirnya diketahui hanyalah sebuah **simulasi komputer** — sebuah dunia yang dibangun dan dijalankan di dalam sistem, bukan realitas sesungguhnya.

Analoginya terasa pas untuk proyek ini: platform ini pada dasarnya juga men-*simulasikan* dan memantau kondisi negara-negara di dunia — cuaca, ekonomi, sentimen berita, risiko rantai pasok — semuanya direkonstruksi dan dipantau lewat data, model skor, dan dashboard, bukan pengamatan langsung. "Dunia" yang dipantau sistem ini adalah representasi data, sama seperti Amphoreus adalah representasi simulasi bagi penghuninya.

## Filosofi Desain UI

Tampilan aplikasi mengadopsi nuansa **ruang kontrol pelabuhan/maritim** (dark navy-ink, aksen sinyal amber/merah/hijau, tipografi *Space Grotesk* + *Inter* + *IBM Plex Mono*) — tapi palet warna dan *vibe* keseluruhannya terinspirasi dari estetika **macOS**.

Pilihan ini personal: sebagai owner proyek, saya merasa UI macOS terlihat jauh lebih profesional, bersih, dan rapi dibanding kebanyakan dashboard admin generik — jadi palet warna dan nuansa itu sengaja diadopsi ke dalam tema gelap platform ini. Alasan lain yang jujur saja cukup manusiawi: proyek ini juga jadi semacam motivasi kecil untuk suatu saat punya laptop Mac sendiri untuk kerja.

## Latar Belakang Proyek

Proyek ini awalnya dikerjakan di repository bernama **`FinalP`**. Namun di tengah proses pengerjaan, sempat ada beberapa kendala teknis dan tekanan mental yang cukup berat ("mental breakdance" — sedikit lelucon getir tapi nyata) yang membuat repository lama itu terasa tidak lagi nyaman untuk dilanjutkan. Sebagai langkah reset, proyek ini dipindahkan sepenuhnya ke repository baru bernama **`RPLUTS`**, dengan struktur yang lebih rapi dan progres yang dibangun ulang secara bertahap dan lebih terkontrol.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel (PHP), MySQL (`webchaindb`) |
| Frontend | Blade, Bootstrap 5, JavaScript ES6, AJAX |
| Visualisasi | Chart.js (grafik tren), Leaflet.js (peta interaktif) |
| Local dev | Laragon |

## Sumber Data Eksternal

| API | Kegunaan |
|---|---|
| REST Countries (countries.dev) | Data dasar negara |
| World Bank API | Indikator ekonomi (GDP, inflasi, populasi, ekspor/impor) |
| Open-Meteo | Data cuaca real-time |
| ExchangeRate API | Kurs mata uang |
| World Port Index | Data pelabuhan dunia |
| GNews API | Berita terkait rantai pasok/ekonomi/geopolitik |

---

## Arsitektur Fitur

### 1. Lapisan Data
Enam `Service` class (satu per API eksternal) + `Console Command` untuk menjalankan fetching secara berkala, menyimpan hasilnya ke cache lokal di MySQL supaya dashboard tidak selalu hit API real-time.

### 2. Lapisan Analitik
- **Sentiment Analysis Engine** — lexicon-based (dictionary kata positif/negatif buatan sendiri, tanpa AI berbayar), menganalisis setiap berita yang masuk.
- **Risk Scoring Engine** — menggabungkan skor cuaca, inflasi, sentimen berita, dan volatilitas kurs dengan bobot yang bisa diatur admin, menghasilkan skor risiko 0–100 per negara (kategori Low/Medium/High).

### 3. Lapisan Presentasi
- **Global Country Dashboard** — pencarian negara, ringkasan risiko/cuaca/ekonomi/berita, dengan hero ticker split-flap menampilkan negara berisiko tertinggi.
- **Global Weather Monitoring** — peta Leaflet dengan marker cuaca per negara.
- **Currency Impact Dashboard** — grafik tren kurs (Chart.js) + tabel kurs terkini, dengan tombol sinkronisasi manual (khusus user yang sudah login) untuk fetch kurs terbaru tanpa perlu buka terminal.
- **Port Location Dashboard** — peta interaktif pelabuhan dunia dengan pencarian nama/UNLOCODE/negara.
- **News Intelligence** — daftar berita dengan badge sentimen (positif/netral/negatif).
- **Country Comparison Engine** — perbandingan 2 negara side-by-side.
- **Watchlist** — user login bisa menandai negara favorit untuk dipantau khusus di satu halaman personal.

### 4. Fitur User & Admin
- Autentikasi berbasis sesi (login/register/logout), role `admin`/`user`.
- **Admin Control Room** — dashboard ringkas admin dengan navigasi terpusat.
- **User Management** — lihat, cari, ubah role, dan hapus akun user (dengan proteksi agar admin tidak bisa mengubah/menghapus akunnya sendiri).
- **Port Management** — CRUD data pelabuhan lewat modal form, tanpa perlu seeding manual.
- **Article Management** — admin bisa menulis artikel analisis manual (terpisah dari berita otomatis GNews), plus tombol sinkronisasi 1-klik untuk menjalankan `fetch:news` dan `analyze:sentiment` tanpa buka terminal.

---

## Skema Database (18 Tabel)

| # | Tabel | Fungsi |
|---|---|---|
| 1 | `users` | Akun user & admin |
| 2 | `countries` | Data dasar negara |
| 3 | `currencies` | Master mata uang |
| 4 | `exchange_rates` | Kurs terkini |
| 5 | `exchange_rate_history` | Histori kurs harian |
| 6 | `economic_indicators` | GDP, inflasi, populasi, ekspor, impor |
| 7 | `weather_snapshots` | Cuaca terkini per negara |
| 8 | `weather_history` | Histori cuaca |
| 9 | `ports` | Data pelabuhan dunia |
| 10 | `news_articles` | Berita hasil fetch GNews + hasil sentimen |
| 11 | `positive_words` | Dictionary kata positif |
| 12 | `negative_words` | Dictionary kata negatif |
| 13 | `risk_weights` | Bobot komponen risk scoring |
| 14 | `risk_scores` | Skor risiko terkini per negara |
| 15 | `risk_score_history` | Histori skor risiko |
| 16 | `watchlists` | Negara favorit tiap user |
| 17 | `articles` | Artikel analisis manual admin |
| 18 | `api_fetch_logs` | Log pemanggilan API eksternal |

---

## Roadmap Pengembangan

- [x] **Fase 1** — Fondasi Database (18 migration, seeder, model)
- [x] **Fase 2** — Integrasi API Eksternal (6 service + command)
- [x] **Fase 3 & 4** — Sentiment Analysis & Risk Scoring Engine
- [x] **Fase 5** — Dashboard & Visualisasi (Country Dashboard, Weather Map, Currency, Ports, News, Comparison)
- [x] **Fase 6** — Fitur User & Admin (auth, watchlist, user/port/article management, sinkronisasi manual)
- [x] **Fase 7** — Finalisasi REST API & Dokumentasi
- [x] **Fase 8** — Testing, Optimasi, & Persiapan Presentasi

**Status: Selesai — seluruh fase pengembangan (Fase 1–8) telah diimplementasikan.**

---

## Setup Lokal (Laragon)

```bash
git clone <url-repo-rpluts>
cd rpluts
composer install
cp .env.example .env
php artisan key:generate
```

Isi kredensial database dan API key di `.env`:

```env
DB_DATABASE=webchaindb
EXCHANGE_RATE_API_KEY=...
GNEWS_API_KEY=...
```

```bash
php artisan migrate --seed
php artisan tinker
>>> User::first()->update(['role' => 'admin']);
```

Akses aplikasi di `http://rpluts.test`.

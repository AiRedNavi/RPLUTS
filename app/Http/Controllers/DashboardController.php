<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * GET /
     * Halaman utama: Global Country Dashboard.
     * Data sebenarnya diambil sisi client lewat dashboard.js (AJAX ke
     * /api/*), controller ini cuma render shell HTML-nya.
     */
    public function index(): View
    {
        return view('dashboard.index');
    }

    /**
     * GET /weather
     * Halaman Global Weather Monitoring (peta interaktif Leaflet.js).
     */
    public function weather(): View
    {
        return view('dashboard.weather-map');
    }

    /**
     * GET /currency
     * Halaman Currency Impact Dashboard (grafik tren kurs Chart.js).
     */
    public function currency(): View
    {
        return view('dashboard.currency');
    }
}
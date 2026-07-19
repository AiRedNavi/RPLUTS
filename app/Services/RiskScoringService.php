<?php

namespace App\Services;

use App\Models\Country;
use App\Models\ExchangeRateHistory;
use App\Models\NewsArticle;
use App\Models\RiskScore;
use App\Models\RiskScoreHistory;
use App\Models\RiskWeight;
use Illuminate\Support\Facades\Log;

class RiskScoringService
{
    /**
     * Hitung ulang skor risiko untuk SEMUA negara di database.
     * Dipanggil dari Command: php artisan risk:calculate
     */
    public function calculateForAllCountries(): int
    {
        $weights = RiskWeight::active();

        if (! $weights) {
            Log::warning('RiskScoringService: tidak ada risk_weights aktif. Jalankan seeder RiskWeightSeeder dulu.');

            return 0;
        }

        $calculated = 0;

        foreach (Country::all() as $country) {
            if ($this->calculateForCountry($country, $weights)) {
                $calculated++;
            }
        }

        return $calculated;
    }

    /**
     * Hitung skor risiko 1 negara dari 4 komponen (weather, inflation,
     * news, currency), kalikan dengan bobot aktif, simpan ke risk_scores
     * dan risk_score_history.
     */
    public function calculateForCountry(Country $country, ?RiskWeight $weights = null): bool
    {
        $weights ??= RiskWeight::active();

        if (! $weights) {
            return false;
        }

        $weatherScore = $this->weatherRiskScore($country);
        $inflationScore = $this->inflationRiskScore($country);
        $newsScore = $this->newsRiskScore($country);
        $currencyScore = $this->currencyRiskScore($country);

        $totalWeight = $weights->weather_weight
            + $weights->inflation_weight
            + $weights->news_weight
            + $weights->currency_weight;

        // Jaga-jaga kalau admin salah input bobot sampai jadi 0
        if ($totalWeight <= 0) {
            $totalWeight = 100;
        }

        $totalScore = (
            ($weatherScore * $weights->weather_weight)
            + ($inflationScore * $weights->inflation_weight)
            + ($newsScore * $weights->news_weight)
            + ($currencyScore * $weights->currency_weight)
        ) / $totalWeight;

        $totalScore = round($totalScore, 2);
        $riskLevel = RiskScore::levelFromScore($totalScore);

        RiskScore::updateOrCreate(
            ['country_id' => $country->id],
            [
                'weather_score' => round($weatherScore, 2),
                'inflation_score' => round($inflationScore, 2),
                'news_score' => round($newsScore, 2),
                'currency_score' => round($currencyScore, 2),
                'total_score' => $totalScore,
                'risk_level' => $riskLevel,
                'calculated_at' => now(),
            ]
        );

        RiskScoreHistory::updateOrCreate(
            [
                'country_id' => $country->id,
                'recorded_date' => now()->toDateString(),
            ],
            [
                'total_score' => $totalScore,
                'risk_level' => $riskLevel,
            ]
        );

        return true;
    }

    /**
     * Komponen 1: Risiko cuaca (0-100), diambil dari storm_risk_level
     * hasil olahan OpenMeteoService. Skala buatan sendiri:
     * low = 15, medium = 55, high = 90.
     */
    protected function weatherRiskScore(Country $country): float
    {
        $level = $country->weatherSnapshot?->storm_risk_level;

        return match ($level) {
            'high' => 90,
            'medium' => 55,
            'low' => 15,
            default => 50, // belum ada data cuaca sama sekali -> netral
        };
    }

    /**
     * Komponen 2: Risiko inflasi (0-100), dari data economic_indicators
     * tahun terbaru. Skala buatan sendiri (semakin tinggi inflasi,
     * semakin tinggi risikonya):
     * <=2%     -> 10   (sangat aman)
     * <=5%     -> 30
     * <=10%    -> 55
     * <=20%    -> 75
     * >20%     -> 95   (sangat berisiko / hiperinflasi)
     */
    protected function inflationRiskScore(Country $country): float
    {
        $indicator = $country->economicIndicators()->latest('year')->first();
        $inflation = $indicator?->inflation_rate;

        if ($inflation === null) {
            return 50; // belum ada data -> netral
        }

        return match (true) {
            $inflation <= 2 => 10,
            $inflation <= 5 => 30,
            $inflation <= 10 => 55,
            $inflation <= 20 => 75,
            default => 95,
        };
    }

    /**
     * Komponen 3: Risiko berita (0-100), rata-rata dari sentimen 10
     * berita terbaru yang terkait negara ini.
     * positive = 15, neutral = 50, negative = 90.
     */
    protected function newsRiskScore(Country $country): float
    {
        $articles = NewsArticle::where('country_id', $country->id)
            ->whereNotNull('sentiment_label')
            ->latest('published_at')
            ->limit(10)
            ->get();

        if ($articles->isEmpty()) {
            return 50; // belum ada berita relevan -> netral
        }

        $scoreMap = ['positive' => 15, 'neutral' => 50, 'negative' => 90];

        $scores = $articles->map(
            fn (NewsArticle $article) => $scoreMap[$article->sentiment_label] ?? 50
        );

        return (float) $scores->avg();
    }

    /**
     * Komponen 4: Risiko volatilitas kurs (0-100), dari histori 7 hari
     * terakhir kurs mata uang negara ini terhadap basis (USD).
     * Semakin besar persentase fluktuasi (max-min terhadap rata-rata),
     * semakin tinggi skornya. 10% fluktuasi dianggap risiko maksimal (100).
     */
    protected function currencyRiskScore(Country $country): float
    {
        if (! $country->currency_id) {
            return 50;
        }

        $rates = ExchangeRateHistory::where('target_currency_id', $country->currency_id)
            ->orderByDesc('recorded_date')
            ->limit(7)
            ->pluck('rate');

        if ($rates->count() < 2) {
            return 50; // data histori belum cukup -> netral
        }

        $max = (float) $rates->max();
        $min = (float) $rates->min();
        $avg = (float) $rates->avg();

        if ($avg <= 0) {
            return 50;
        }

        $volatilityPercent = (($max - $min) / $avg) * 100;

        return min(100, max(0, $volatilityPercent * 10));
    }
}
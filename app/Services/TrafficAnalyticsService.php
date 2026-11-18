<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class TrafficAnalyticsService
{
    public const ALLOWED_RANGES = [7, 28, 90];

    public function summary(int $days = 7): array
    {
        $days = in_array($days, self::ALLOWED_RANGES, true) ? $days : 7;

        try {
            // Prefix db digunakan agar tidak membaca cache lama dari GA4.
            return Cache::remember(
                "traffic.db.summary.{$days}",
                now()->addMinutes(5),
                fn () => $this->build($days)
            );
        } catch (Throwable $e) {
            report($e);

            return [
                'error' => true,
                'message' => 'Data traffic belum bisa diambil dari database.'
            ];
        }
    }

    private function build(int $days): array
    {
        $today = Carbon::today();

        $currentStart = $today->copy()->subDays($days - 1)->startOfDay();
        $currentEnd = $today->copy()->endOfDay();

        $previousStart = $currentStart->copy()->subDays($days);
        $previousEnd = $currentStart->copy()->subSecond();

        $current = $this->periodSummary($currentStart, $currentEnd);
        $previous = $this->periodSummary($previousStart, $previousEnd);

        $dailyRows = DB::table('website_page_views')
            ->selectRaw('DATE(viewed_at) AS date')
            ->selectRaw('COUNT(*) AS views')
            ->selectRaw('COUNT(DISTINCT visitor_id) AS visitors')
            ->whereBetween('viewed_at', [$currentStart, $currentEnd])
            ->groupByRaw('DATE(viewed_at)')
            ->orderByRaw('DATE(viewed_at)')
            ->get()
            ->keyBy('date');

        // Mengisi tanggal kosong dengan nilai 0 agar grafik konsisten.
        $labels = [];
        $views = [];
        $visitors = [];

        for ($date = $currentStart->copy(); $date->lte($currentEnd); $date->addDay()) {
            $key = $date->toDateString();
            $row = $dailyRows->get($key);

            $labels[] = $date->translatedFormat('d M');
            $views[] = (int) ($row->views ?? 0);
            $visitors[] = (int) ($row->visitors ?? 0);
        }

        // Halaman terpopuler berdasarkan path.
        // Kolom title belum digunakan karena saat ini masih bernilai NULL.
        $topPages = DB::table('website_page_views')
            ->select('path')
            ->selectRaw('COUNT(*) AS total')
            ->whereBetween('viewed_at', [$currentStart, $currentEnd])
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->path ?: '/',
                'value' => (int) $row->total,
            ])
            ->values()
            ->all();

        // Sumber trafik berdasarkan source pada website_sessions.
        $referrers = DB::table('website_sessions')
            ->selectRaw("COALESCE(NULLIF(source, ''), 'direct') AS source")
            ->selectRaw('COUNT(*) AS total')
            ->whereBetween('started_at', [$currentStart, $currentEnd])
            ->groupByRaw("COALESCE(NULLIF(source, ''), 'direct')")
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->source,
                'value' => (int) $row->total,
            ])
            ->values()
            ->all();

        return [
            'error' => false,
            'days' => $days,

            'kpi' => [
                'visitors' => $this->kpi(
                    $current['visitors'],
                    $previous['visitors']
                ),

                'views' => $this->kpi(
                    $current['views'],
                    $previous['views']
                ),

                'sessions' => $this->kpi(
                    $current['sessions'],
                    $previous['sessions']
                ),

                'duration' => $this->kpi(
                    $current['duration'],
                    $previous['duration'],
                    true
                ),
            ],

            'chart' => [
                'labels' => $labels,
                'views' => $views,
                'visitors' => $visitors,
            ],

            'top_pages' => $topPages,
            'referrers' => $referrers,

            'updated_at' => now()->translatedFormat('d M Y H:i'),
        ];
    }

    private function periodSummary(Carbon $start, Carbon $end): array
    {
        $views = DB::table('website_page_views')
            ->whereBetween('viewed_at', [$start, $end]);

        $totalViews = (clone $views)->count();

        $totalVisitors = (clone $views)
            ->distinct('visitor_id')
            ->count('visitor_id');

        $totalSessions = DB::table('website_sessions')
            ->whereBetween('started_at', [$start, $end])
            ->count();

        /*
         * Estimasi durasi sesi:
         * jumlahkan active_seconds (waktu tab benar-benar aktif) dari
         * seluruh page view dalam satu sesi, kemudian hitung rata-ratanya.
         * duration_seconds tidak dipakai karena ikut menghitung tab yang
         * dibiarkan terbuka.
         *
         * Sesi tanpa waktu aktif (belum ada data dari browser) tidak dimasukkan.
         */
        $sessionDurations = DB::table('website_page_views')
            ->select('session_id')
            ->selectRaw('SUM(active_seconds) AS total_duration')
            ->whereBetween('viewed_at', [$start, $end])
            ->where('active_seconds', '>', 0)
            ->groupBy('session_id');

        $averageDuration = DB::query()
            ->fromSub($sessionDurations, 'session_durations')
            ->avg('total_duration');

        return [
            'visitors' => $totalVisitors,
            'views' => $totalViews,
            'sessions' => $totalSessions,
            'duration' => (float) ($averageDuration ?? 0),
        ];
    }

    private function kpi(
        float|int $current,
        float|int $previous,
        bool $isDuration = false
    ): array {
        $change = $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : null;

        return [
            'value' => $isDuration
                ? $this->duration((float) $current)
                : (int) $current,

            'change' => $change,
        ];
    }

    private function duration(float $seconds): string
    {
        $seconds = (int) round($seconds);

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return "{$hours}j {$minutes}m";
        }

        if ($minutes > 0) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        return "{$remainingSeconds}s";
    }
}
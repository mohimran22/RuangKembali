<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebsiteAnalyticsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya request halaman GET
        if (
            !$request->isMethod('GET') ||
            $request->is('sitemap.xml') ||
            $request->expectsJson()
        ) {
            return $next($request);
        }
        $pageViewId = (string) Str::uuid();

        $request->attributes->set(
            'analytics_page_view_id',
            $pageViewId
        );
        $response = $next($request);

        // Hanya catat halaman HTML yang berhasil dibuka
        if (
            $response->getStatusCode() !== 200 ||
            !str_contains(
                strtolower($response->headers->get('Content-Type', '')),
                'text/html'
            )
        ) {
            return $response;
        }

        // Abaikan bot, crawler, pengecek link preview, dan monitoring
        $userAgent = $request->userAgent() ?? '';

        if (
            $userAgent === '' ||
            preg_match(
                '/bot|crawl|spider|headless|curl|wget|python|java|go-http|scrapy|axios|node-fetch|lighthouse|pingdom|uptime|monitor|facebookexternalhit|whatsapp|preview/i',
                $userAgent
            )
        ) {
            return $response;
        }

        // Abaikan karyawan/admin yang sedang login saat membuka website publik
        if ($request->user()) {
            return $response;
        }

        $now = now();

        // Identitas anonim pengunjung
        $visitorKey = $request->cookie('aa_visitor');

        if (!Str::isUuid($visitorKey)) {
            $visitorKey = (string) Str::uuid();
        }

        // Identitas sesi
        $sessionKey = $request->cookie('aa_session');

        // Pencatatan statistik tidak boleh membuat halaman publik error.
        // Kalau database bermasalah, laporkan saja dan tampilkan halaman seperti biasa.
        try {
        DB::transaction(function () use (
            $request,
            $visitorKey,
            &$sessionKey,
            $userAgent,
            $now,
            $pageViewId
        ) {
            // Cari atau buat pengunjung
            DB::table('website_visitors')->insertOrIgnore([
                'id' => (string) Str::uuid(),
                'visitor_key' => $visitorKey,
                'device_type' => $this->deviceType($userAgent),
                'browser' => $this->browser($userAgent),
                'operating_system' => $this->operatingSystem($userAgent),
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $visitor = DB::table('website_visitors')
                ->where('visitor_key', $visitorKey)
                ->first();

            DB::table('website_visitors')
                ->where('id', $visitor->id)
                ->update([
                    'last_seen_at' => $now,
                    'updated_at' => $now,
                ]);

            // Periksa apakah sesi masih aktif (30 menit)
            $session = null;

            if (Str::isUuid($sessionKey)) {
                $session = DB::table('website_sessions')
                    ->where('session_key', $sessionKey)
                    ->where('visitor_id', $visitor->id)
                    ->first();
            }

 
$sessionExpired = !$session;

if ($session) {
    $lastActivity = \Carbon\Carbon::parse(
        $session->last_activity_at
    );

    $sessionExpired = $lastActivity->diffInSeconds($now) >= 1800;
}

if ($sessionExpired) {
    // Buat session baru
    $sessionKey = (string) Str::uuid();

    $referrer = $request->headers->get('referer');

    $referrerHost = $referrer
        ? parse_url($referrer, PHP_URL_HOST)
        : null;

    // Referrer dari domain sendiri (mis. sesi baru setelah 30 menit idle)
    // dianggap direct, bukan sumber trafik.
// Referrer dari domain sendiri dianggap direct (www dan non-www sama saja)
    $ownHost = preg_replace('/^www\./', '', $request->getHost());
    $refHost = $referrerHost ? preg_replace('/^www\./', '', $referrerHost) : null;

    if ($refHost && ($refHost === $ownHost || str_ends_with($refHost, '.' . $ownHost))) {
        $referrerHost = null;
    }

    $source = $request->query('utm_source')
        ?: ($referrerHost ?: 'direct');

    $medium = $request->query('utm_medium')
        ?: ($referrerHost ? 'referral' : 'none');

    DB::table('website_sessions')->insert([
        'id' => (string) Str::uuid(),
        'visitor_id' => $visitor->id,
        'session_key' => $sessionKey,
        'landing_page' => $request->path(),
        'referrer' => $referrerHost,
        'source' => $source,
        'medium' => $medium,
        'campaign' => $request->query('utm_campaign'),
        'started_at' => $now,
        'last_activity_at' => $now,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $session = DB::table('website_sessions')
        ->where('session_key', $sessionKey)
        ->where('visitor_id', $visitor->id)
        ->first();

    // Pastikan session berhasil dibuat
    if (!$session) {
        throw new \RuntimeException(
            'Analytics session gagal dibuat.'
        );
    }

} else {
    // Perbarui aktivitas session yang masih aktif
    DB::table('website_sessions')
        ->where('id', $session->id)
        ->update([
            'last_activity_at' => $now,
            'updated_at' => $now,
        ]);
}


            // Simpan page view
            DB::table('website_page_views')->insert([
                'id' => $pageViewId,
                'visitor_id' => $visitor->id,
                'session_id' => $session->id,
                'path' => '/' . ltrim($request->path(), '/'),
                'title' => null,
                'referrer' => $request->headers->get('referer')
                    ? parse_url($request->headers->get('referer'), PHP_URL_HOST)
                    : null,
                'viewed_at' => $now,
                'duration_seconds' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
        } catch (\Throwable $e) {
            report($e);

            return $response;
        }

        // Cookie pengunjung: berlaku satu tahun
        $response->headers->setCookie(cookie(
            'aa_visitor',
            $visitorKey,
            525600,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'Lax'
        ));

        // Cookie sesi: 30 menit
        $response->headers->setCookie(cookie(
            'aa_session',
            $sessionKey,
            30,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'Lax'
        ));

        return $response;
    }

    private function deviceType(string $ua): string
    {
        if (preg_match('/tablet|ipad/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function browser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Other',
        };
    }

    private function operatingSystem(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone'),
            str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Other',
        };
    }
}
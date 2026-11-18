<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WebsiteAnalyticsController extends Controller
{

public function track(Request $request)
{
    $validator = Validator::make($request->all(), [
        'page_view_id' => ['required', 'uuid'],
        'duration_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
        'active_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
        'max_scroll_percent' => ['required', 'integer', 'min:0', 'max:100'],
        'interaction_count' => ['required', 'integer', 'min:0', 'max:100000'],
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Invalid data'], 422);
    }

    // Cukup cookie visitor (berlaku 1 tahun). Cookie sesi hanya 30 menit,
    // sehingga bacaan panjang akan kehilangan data kalau dipakai di sini.
    // Kepemilikan page view tetap aman karena page_view_id berupa UUID
    // dan dicocokkan dengan visitor_id.
    $visitorKey = $request->cookie('aa_visitor');

    if (!Str::isUuid($visitorKey)) {
        return response()->json(['message' => 'Invalid visitor'], 401);
    }

    $data = $validator->validated();
    $now = now();

    $visitor = DB::table('website_visitors')
        ->where('visitor_key', $visitorKey)
        ->first();

    if (!$visitor) {
        return response()->json(['message' => 'Visitor not found'], 404);
    }

    return DB::transaction(function () use (
        $data,
        $visitor,
        $now
    ) {
        // Ambil page view dan kunci record selama transaksi.
        $pageView = DB::table('website_page_views')
            ->where('id', $data['page_view_id'])
            ->where('visitor_id', $visitor->id)
            ->lockForUpdate()
            ->first();

        if (!$pageView) {
            return response()->json([
                'message' => 'Page view not found',
            ], 404);
        }

        // Durasi maksimal berdasarkan waktu yang diketahui server.
        $viewedAt = \Carbon\Carbon::parse($pageView->viewed_at);

        // Cast ke int: Carbon 3 mengembalikan desimal, dan kolom integer
        // di Postgres menolak nilai desimal.
        $serverDuration = max(
            0,
            (int) $viewedAt->diffInSeconds($now)
        );

        // Beri toleransi 5 detik untuk perbedaan waktu client-server.
        $duration = min(
            (int) $data['duration_seconds'],
            $serverDuration + 5
        );

        // Waktu aktif tidak boleh melebihi durasi halaman.
        $active = min(
            (int) $data['active_seconds'],
            $duration
        );

        $scroll = (int) $data['max_scroll_percent'];
        $interactions = (int) $data['interaction_count'];

        $engaged = (
            $active >= 10 ||
            $interactions > 0 ||
            $scroll >= 25
        );

        // Periksa apakah ada aktivitas baru dibandingkan data sebelumnya.
        $hasNewActivity = (
            $active > (int) $pageView->active_seconds ||
            $interactions > (int) $pageView->interaction_count ||
            $scroll > (int) $pageView->max_scroll_percent
        );

        DB::table('website_page_views')
            ->where('id', $pageView->id)
            ->update([
                'duration_seconds' => max(
                    (int) ($pageView->duration_seconds ?? 0),
                    $duration
                ),

                'active_seconds' => max(
                    (int) $pageView->active_seconds,
                    $active
                ),

                'max_scroll_percent' => max(
                    (int) $pageView->max_scroll_percent,
                    $scroll
                ),

                'interaction_count' => max(
                    (int) $pageView->interaction_count,
                    $interactions
                ),

                'is_engaged' => (
                    (bool) $pageView->is_engaged || $engaged
                ),

                'last_activity_at' => $hasNewActivity
                    ? $now
                    : $pageView->last_activity_at,

                'updated_at' => $now,
            ]);

        return response()->json([
            'success' => true,
        ]);
    });
}

}
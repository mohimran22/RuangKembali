<?php

namespace App\Http\Controllers;

use App\Services\TrafficAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrafficController extends Controller
{
    public function __invoke(Request $request, TrafficAnalyticsService $service): JsonResponse
    {
        $days = (int) $request->query('days', 7);

        return response()->json($service->summary($days));
    }
}
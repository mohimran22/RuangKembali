<?php

namespace App\Http\Controllers;

use App\Models\Event;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::published()
            ->with('category')
            ->where('end_at', '>=', now())
            ->orderBy('start_at')
            ->limit(6)
            ->get();

        return view('welcome', compact('events'));
    }
}
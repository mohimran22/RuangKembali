<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct() {
    
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
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
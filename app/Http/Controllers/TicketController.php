<?php

namespace App\Http\Controllers;

use App\Models\EventRegistration;

class TicketController extends Controller
{
    public function show(EventRegistration $registration)
    {
        $registration->load('event', 'user');

        return response()
            ->view('tickets.public', [
                'registration' => $registration,
                'event'        => $registration->event,
            ])
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
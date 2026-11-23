<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
public function download(Event $event, Request $request)
{
    $registration = $event->registrations()
        ->where('user_id', $request->user()->id)
        ->whereNotNull('checked_in_at')
        ->firstOrFail();

    abort_unless($registration->canDownloadCertificate(), 403, 'Sertifikat belum tersedia.');

    if (! $registration->certificate_code) {
        do {
            $code = Str::upper(Str::random(10));
        } while (EventRegistration::where('certificate_code', $code)->exists());

        $registration->update(['certificate_code' => $code]);
    }

    $pdf = Pdf::loadView('certificates.template', [
        'event'        => $event->load('certificate'),
        'registration' => $registration,
        'name'         => $registration->participant_name,
        'verifyUrl'    => route('certificates.verify', $registration->certificate_code),
    ])->setPaper('a4', 'landscape');

    return $pdf->download('sertifikat-' . $event->slug . '.pdf');
}

public function verify(string $code)
{
    $registration = EventRegistration::with('event')
        ->where('certificate_code', $code)
        ->whereNotNull('checked_in_at')
        ->firstOrFail();

    return view('certificates.verify', [
        'name'  => $registration->participant_name,
        'event' => $registration->event,
    ]);
}
}
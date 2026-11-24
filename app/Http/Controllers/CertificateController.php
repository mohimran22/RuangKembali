<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function download(Event $event, Request $request)
    {
        $event->load('certificate');

        $registration = $event->registrations()
            ->where('user_id', $request->user()->id)
            ->where('status', 'attended')
            ->firstOrFail();

        $registration->setRelation('event', $event);
        abort_unless($registration->canDownloadCertificate(), 403, 'Sertifikat belum tersedia.');

        $cert = $event->certificate;
        $path = storage_path('app/public/' . $cert->template);
        abort_unless(is_file($path), 404, 'Desain sertifikat tidak ditemukan.');

        if (! $registration->certificate_code) {
            do {
                $code = Str::upper(Str::random(10));
            } while (EventRegistration::where('certificate_code', $code)->exists());

            $registration->update(['certificate_code' => $code]);
            $registration->refresh();
        }

        // Ukuran halaman mengikuti rasio gambar desain
        [$px, $py] = getimagesize($path);
        $w = 842;                                   // lebar A4 landscape (pt)
        $h = (int) round($w * $py / $px);

        // Nama panjang: kecilkan huruf otomatis
        $name = $registration->participant_name;
        $size = (int) ($cert->name_size ?? 40);
        if (mb_strlen($name) > 25) {
            $size = max(16, (int) round($size * 25 / mb_strlen($name)));
        }

        return Pdf::loadView('certificates.template', [
            'path'   => $path,
            'w'      => $w,
            'h'      => $h,
            'name'   => $name,
            'size'   => $size,
            'color'  => $cert->name_color ?? '#000000',
            'top'    => $h * ((int) ($cert->name_top ?? 50)) / 100,
            'code'   => $registration->certificate_code,
            'verify' => route('certificates.verify', $registration->certificate_code),
        ])->setPaper([0, 0, $w, $h])
          ->download('sertifikat-' . $event->slug . '.pdf');
    }

    public function verify(string $code)
    {
        $registration = EventRegistration::with('event')
            ->where('certificate_code', $code)
            ->where('status', 'attended')
            ->firstOrFail();

        return view('certificates.verify', [
            'name'  => $registration->participant_name,
            'event' => $registration->event,
        ]);
    }
}
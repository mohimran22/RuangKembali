<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    /**
     * Verifikasi token Cloudflare Turnstile ke endpoint siteverify.
     * Gagal-tertutup: jika verifikasi tidak bisa diproses, pendaftaran ditolak.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret'   => config('services.turnstile.secret_key'),
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if (! $response->json('success')) {
                $fail('Verifikasi keamanan gagal. Silakan coba lagi.');
            }
        } catch (\Throwable $e) {
            report($e);
            $fail('Verifikasi keamanan tidak dapat diproses. Silakan coba lagi sebentar lagi.');
        }
    }
}
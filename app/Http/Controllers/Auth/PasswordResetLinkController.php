<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'cf-turnstile-response' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $response = Http::asForm()->post(
                        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                        [
                            'secret'   => config('services.turnstile.secret_key'),
                            'response' => $value,
                            'remoteip' => $request->ip(),
                        ]
                    );

                    if (! $response->json('success')) {
                        $fail('Verifikasi keamanan gagal, silakan coba lagi.');
                    }
                },
            ],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'cf-turnstile-response.required' => 'Selesaikan verifikasi keamanan terlebih dahulu.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
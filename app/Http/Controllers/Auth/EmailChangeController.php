<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailChangeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Hanya untuk akun yang belum terverifikasi
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->merge(['email' => strtolower(trim($request->email))]);

        $request->validate([
            'email' => [
                'required', 'string', 'lowercase', 'email:rfc,dns', 'max:255',
                'unique:users,email,' . $user->id,
                function ($attribute, $value, $fail) {
                    $typos = ['gmai.com', 'gmial.com', 'gmail.co', 'gamil.com', 'yahooo.com', 'yaho.com'];
                    if (in_array(substr(strrchr($value, '@'), 1), $typos, true)) {
                        $fail('Domain email sepertinya salah ketik. Maksud Anda gmail.com?');
                    }
                },
            ],
        ]);

        $user->forceFill([
            'email' => $request->email,
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }
}
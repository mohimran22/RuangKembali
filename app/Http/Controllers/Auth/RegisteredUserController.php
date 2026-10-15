<?php

// Simpan sebagai: app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Honeypot: kolom "website" disembunyikan dari manusia, bot biasanya mengisinya.
        // Wajib menambahkan input-nya di resources/views/auth/register.blade.php (lihat catatan).
        if ($request->filled('website')) {
            abort(422);
        }

        $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            // Opsional: format nomor HP Indonesia. Longgarkan jika Anda menerima nomor luar negeri.
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => 'Format nomor HP tidak valid. Contoh: 081234567890.',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $memberRole = Role::findByName('Member');
            $user->assignRole($memberRole);

            // Customer TIDAK dibuat di sini lagi.
            // Dibuat oleh listener BuatCustomerSetelahVerifikasi setelah email terverifikasi.

            $user->active_role = $memberRole->id;
            $user->save();

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
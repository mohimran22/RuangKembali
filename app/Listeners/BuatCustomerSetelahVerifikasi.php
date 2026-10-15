<?php

namespace App\Listeners;

use App\Models\Customer;
use Illuminate\Auth\Events\Verified;

class BuatCustomerSetelahVerifikasi
{
    /**
     * Dipanggil saat pengguna mengklik link verifikasi email.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        // Cegah duplikat: pengguna lama sudah punya customer, dan event bisa
        // terdaftar dua kali (auto-discovery + EventServiceProvider).
        if (Customer::where('user_id', $user->id)->exists()) {
            return;
        }

        Customer::create([
            'user_id' => $user->id,
            ...Customer::getDefaultAttributes($user),
        ]);
    }
}
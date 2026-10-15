<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use App\Helpers\ActiveRole;
use TakiElias\Tablar\Tablar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use App\View\Composers\NavbarComposer;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
                URL::forceScheme('https');
            }
            view()->composer('*', function ($view) {
            $tablar = app(Tablar::class);
            $view->with('tablar', $tablar);
        });

        Blade::if('activerole', function ($roleName) {
            return strtolower(ActiveRole::name()) === strtolower($roleName);
        });

        Blade::if('activeperm', function ($permission) {
            return ActiveRole::hasPermission($permission);
        });
        Carbon::setLocale('id');
        View::composer('partials.navbar', NavbarComposer::class);

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verifikasi Alamat Email Anda - Ruang Kembali')
                ->greeting('Assalamualaikum, ' . $notifiable->fullname . '!')
                ->line('Terima kasih sudah mendaftar di Ruang Kembali.')
                ->line('Silakan klik tombol di bawah untuk memverifikasi alamat email kamu.')
                ->action('Verifikasi Email', $url)
                ->line('Jika kamu tidak merasa membuat akun ini, abaikan email ini.')
                ->salutation('Salam, Tim Ruang Kembali');
        });

        RateLimiter::for('registrasi', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return [
                // Maks 3 percobaan per menit per IP
                Limit::perMinute(3)->by('reg-ip-min:' . $request->ip()),
                // Maks 10 percobaan per jam per IP
                Limit::perHour(20)->by('reg-ip-hour:' . $request->ip()),
                // Maks 2 percobaan per hari untuk alamat email yang sama
                // (mencegah email bombing ke satu korban)
                Limit::perDay(5)->by('reg-email:' . sha1($email)),
            ];
        });

        // Limiter untuk kirim ulang email verifikasi
        RateLimiter::for('kirim-verifikasi', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return [
                Limit::perMinute(1)->by('verif-min:' . $key),
                Limit::perHour(5)->by('verif-hour:' . $key),
            ];
        });
    }
}
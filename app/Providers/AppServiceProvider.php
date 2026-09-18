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
    }
}
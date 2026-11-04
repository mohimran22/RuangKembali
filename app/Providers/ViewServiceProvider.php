<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Models\Menu;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    View::composer('tablar::partials.navbar.sidebar', function ($view) {
        $user = Auth::user();
        if (!$user) return;

        $cacheKey = 'menus_for_user_' . $user->id . '_' . $user->active_role;

        $menus = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($user) {
            return Menu::whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('order')
                ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('order')])
                ->get()
                ->filter(fn($menu) => $menu->isVisibleFor($user))
                ->map(function ($menu) {
                    $menuArray = $menu->toArray(); // convert ke array biar aman di Blade
                    $menuArray['children'] = $menu->children->toArray();
                    return $menuArray;
                })
                ->values()
                ->toArray(); // biar hasil akhir array, bukan Collection
        });

        $view->with('menus', $menus);
    });
}

}
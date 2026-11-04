<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth; 

class Menu extends Model
{
    protected $fillable = [
        'parent_id', 'text', 'icon', 'url', 'type', 'order', 'is_active', 'permission_name'
    ];

    // Parent / children
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    public function isVisibleFor($user)
    {
        // Jika tidak ada permission_name, menu selalu tampil
        if (empty($this->permission_name)) {
            return true;
        }

        // Pecah permission yang dipisahkan dengan "|"
        $permissions = explode('|', $this->permission_name);

        // Jika user punya salah satu permission, tampilkan
        foreach ($permissions as $perm) {
            if ($user->can(trim($perm))) {
                return true;
            }
        }

        return false;
    }

    public function getLinkAttribute()
    {
        if ($this->type === 'route' && $this->url && \Route::has($this->url)) {
            return route($this->url);
        }
        return $this->url ?? '#';
    }

     protected static function booted()
    {
        static::saved(function () {
            self::clearMenuCache();
        });

        static::deleted(function () {
            self::clearMenuCache();
        });
    }

    // ==========================
    // 🔹 Fungsi untuk hapus cache semua user
    // ==========================
    protected static function clearMenuCache()
    {
        // Kalau kamu cache per user
        $users = \App\Models\User::all(['id']);
        foreach ($users as $user) {
            Cache::forget('menus_for_user_' . $user->id);
        }
    }
}

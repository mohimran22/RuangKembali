<?php


use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

if (!function_exists('route_exists')) {
    function route_exists($name)
    {
        return Route::has($name);
    }
}

if (! function_exists('uniqueSlug')) {
    function uniqueSlug(string $value, string $model, ?string $ignoreId = null): string
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $count = 1;

        while (
            $model::where('slug', $slug)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}
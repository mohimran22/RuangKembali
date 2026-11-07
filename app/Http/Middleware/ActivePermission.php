<?php

namespace App\Http\Middleware;

use App\Helpers\ActiveRole;
use Closure;
use Illuminate\Http\Request;

class ActivePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        // Mendukung format Spatie: 'izin a|izin b'
        $list = collect($permissions)
            ->flatMap(fn ($p) => explode('|', $p));

        abort_unless(
            $list->contains(fn ($p) => ActiveRole::hasPermission($p)),
            403
        );

        return $next($request);
    }
}
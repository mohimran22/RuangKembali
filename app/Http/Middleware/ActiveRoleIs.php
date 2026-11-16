<?php

namespace App\Http\Middleware;

use App\Helpers\ActiveRole;
use Closure;
use Illuminate\Http\Request;

class ActiveRoleIs
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        // Super-Admin lolos semua pengecekan role
        if (ActiveRole::name() === 'Super-Admin') {
            return $next($request);
        }

        $allowed = collect($roles)->flatMap(fn ($r) => explode('|', $r));

        abort_unless($allowed->contains(ActiveRole::name()), 403);

        return $next($request);
    }
}
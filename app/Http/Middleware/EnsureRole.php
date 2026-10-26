<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && ($user->role === 'admin' || in_array($user->role, $roles))) {
            return $next($request);
        }

        abort(403, "Vous n'avez pas accès à cette page.");
    }
}
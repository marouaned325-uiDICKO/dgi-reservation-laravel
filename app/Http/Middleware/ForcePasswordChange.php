<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->doit_changer_mot_de_passe) {

            if (
                !$request->routeIs('mot-de-passe.changer') &&
                !$request->routeIs('mot-de-passe.mettre-a-jour') &&
                !$request->routeIs('logout')
            ) {
                return redirect()->route('mot-de-passe.changer');
            }
        }

        return $next($request);
    }
}
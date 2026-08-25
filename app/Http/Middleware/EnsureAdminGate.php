<?php

namespace App\Http\Middleware;

use App\Support\AdminAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminGate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminAuth::gateAllows($request)) {
            return redirect('/');
        }

        return $next($request);
    }
}

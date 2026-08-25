<?php

namespace App\Http\Middleware;

use App\Support\AdminAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminAuth::isUnlocked($request)) {
            return redirect()->route('admin.unlock');
        }

        return $next($request);
    }
}

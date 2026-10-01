<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!auth()->user()->hasModuleAccess($module)) {
            abort(403, 'You do not have access to this module.');
        }

        return $next($request);
    }
}
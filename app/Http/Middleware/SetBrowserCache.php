<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class SetBrowserCache
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Cache publicly for 1 hour
        $response->headers->set('Cache-Control', 'private, max-age=3600');

        return $response;
    }
}

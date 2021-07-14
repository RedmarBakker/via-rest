<?php

namespace ViaRest\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeInjectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        app('events')->listen('me-injection-check', function($parameters) {
            return array_merge($parameters, [Auth::user()->id]);
        });

        return $next($request);
    }
}

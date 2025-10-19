<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasRight
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $right): Response
    {
        if (!$request->user() || !$request->user()->hasRight($right, $request->cinema)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

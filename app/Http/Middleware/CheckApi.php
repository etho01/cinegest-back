<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $api = CinemaApi::where('apiKey', $request->header('X-API-KEY'))->first();
        if (!$api) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->attributes->set('cinemaApi', $api);

        return $next($request);
    }
}

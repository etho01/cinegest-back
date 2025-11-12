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
        $user = $request->user();

        if (!$user) 
        {
            abort(401, 'Unauthorized action.');
        }

        $cinema = $request->cinema;
        $cinemaId = $cinema ? $cinema->id : null;

        if (!$user->hasRight($right, $cinemaId)) 
        {
            if ($user->isSuperAdmin()) 
            {
                return $next($request);
            }
            
            abort(403, 'Forbidden action.');
        }

        return $next($request);
    }
}

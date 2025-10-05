<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class MultiGuardAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    protected $guards = ['api', 'admin'];


    public function handle(Request $request, Closure $next): Response
    {
        foreach ($this->guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Set the default guard for the rest of the request
                Auth::shouldUse($guard);
                $request->attributes->set('auth_type', $guard);
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }
}

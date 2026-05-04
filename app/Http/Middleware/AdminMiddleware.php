<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            if (!Auth::check()) {
                return redirect('/login')->with('error', 'Please log in to continue.');
            }
            abort(403, 'Access denied. Admin only area.');
        }

        return $next($request);
    }
}

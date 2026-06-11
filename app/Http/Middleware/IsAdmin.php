<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        // Return 403 Forbidden or redirect to dashboard
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized action. Admin access required.'], 403);
        }
        
        return redirect()->route('dashboard')->with('error', 'You do not have permission to access the admin area.');
    }
}

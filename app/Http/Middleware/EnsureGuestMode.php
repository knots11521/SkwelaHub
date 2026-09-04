<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->hasApprovedSchoolMembership() && ! $user->hasRole('Super Admin')) {
            if ($request->routeIs('join-school') || $request->routeIs('dashboard') || $request->routeIs('invite.accept')) {
                return $next($request);
            }

            return redirect()->route('join-school');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\SchoolRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanJoinSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user->canAccessJoinSchool()) {
            if ($user->hasRole(SchoolRole::Teacher->value)) {
                return redirect()->route('dashboard')
                    ->with('info', __('You have already joined a school.'));
            }

            abort(403);
        }

        return $next($request);
    }
}

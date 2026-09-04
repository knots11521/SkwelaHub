<?php

namespace App\Actions\Fortify;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasApprovedSchoolMembership() || $user->hasPendingSchoolMembership()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('join-school');
    }
}

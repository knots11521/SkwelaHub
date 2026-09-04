<?php

namespace App\Actions\Fortify;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $token = $request->query('invite_token');

        if ($token) {
            return redirect()->route('invite.accept', $token);
        }

        return redirect()->route('dashboard');
    }
}

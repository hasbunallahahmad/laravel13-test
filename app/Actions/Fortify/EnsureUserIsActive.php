<?php

namespace App\Actions\Fortify;

use Closure;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;

class EnsureUserIsActive
{
    public function __invoke(Request $request, Closure $next): mixed
    {
        $email = $request->input(Fortify::username());

        if (! is_string($email) || $email === '') {
            return $next($request);
        }

        $user = config('auth.providers.users.model')::query()
            ->where('email', mb_strtolower($email))
            ->first();

        if ($user && ! $user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        return $next($request);
    }
}

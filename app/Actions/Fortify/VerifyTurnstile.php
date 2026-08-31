<?php

namespace App\Actions\Fortify;

use App\Services\Security\TurnstileService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyTurnstile
{
    public function __construct(
        private readonly TurnstileService $turnstile,
    ) {}

    public function __invoke(
        Request $request,
        Closure $next,
    ): mixed {
        $token = $request->input('cf-turnstile-response');

        if (! is_string($token) || $token === '') {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => [
                    __('Security verification is required. Please try again.'),
                ],
            ]);
        }

        if (! $this->turnstile->verify(
            $token,
            $request->ip(),
        )) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => [
                    __('Security verification failed. Please try again.'),
                ],
            ]);
        }

        return $next($request);
    }
}

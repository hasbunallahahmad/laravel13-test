<?php

namespace App\Services\Security;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TurnstileService
{
    public function verify(
        string $token,
        ?string $remoteIp = null,
    ): bool {
        if ($token === '') {
            return false;
        }

        $secretKey = config('services.turnstile.secret_key');
        $verifyUrl = config('services.turnstile.verify_url');

        if (! is_string($secretKey) || $secretKey === '') {
            return false;
        }

        if (! is_string($verifyUrl) || $verifyUrl === '') {
            return false;
        }

        $payload = [
            'secret' => $secretKey,
            'response' => $token,
        ];

        if (is_string($remoteIp) && $remoteIp !== '') {
            $payload['remoteip'] = $remoteIp;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post($verifyUrl, $payload);
        } catch (ConnectionException) {
            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        return $response->json('success') === true;
    }
}

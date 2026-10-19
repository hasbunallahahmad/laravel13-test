<?php

declare(strict_types=1);

namespace App\Services\Menu;

final class MenuUrlValidator
{
    /**
     * Validate local paths and absolute HTTP(S) URLs before they are stored
     * or rendered as public menu destinations.
     */
    public function isValid(string $url): bool
    {
        $url = trim($url);

        if (
            $url === ''
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $url)
        ) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return $this->isValidLocalPath($url);
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($scheme, ['http', 'https'], true)
            && is_string($host)
            && $host !== ''
            && parse_url($url, PHP_URL_USER) === null
            && parse_url($url, PHP_URL_PASS) === null;
    }

    private function isValidLocalPath(string $url): bool
    {
        // Parse before decoding so query strings and fragments cannot alter
        // how the path is interpreted.
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/')) {
            return false;
        }

        $decodedPath = $path;
        $fullyDecoded = false;

        // Decode a bounded number of times and reject excessive nesting.
        for ($i = 0; $i < 10; $i++) {
            if (
                str_starts_with($decodedPath, '//')
                || preg_match('/[\\x00-\\x1F\\x7F\\\\\\\\]/', $decodedPath)
            ) {
                return false;
            }

            $nextDecodedPath = rawurldecode($decodedPath);

            if ($nextDecodedPath === $decodedPath) {
                $fullyDecoded = true;
                break;
            }

            $decodedPath = $nextDecodedPath;
        }

        if (
            ! $fullyDecoded
            || str_starts_with($decodedPath, '//')
            || preg_match('/[\\x00-\\x1F\\x7F\\\\\\\\]/', $decodedPath)
        ) {
            return false;
        }

        // Reject dot segments so path normalization cannot escape a public
        // path and reach a protected route.
        foreach (explode('/', $decodedPath) as $segment) {
            if ($segment === '.' || $segment === '..') {
                return false;
            }
        }

        // These route families require authentication and must not be linked
        // from public navigation, even if entered as a local URL.
        return ! preg_match(
            '#^/(?:admin|dashboard|settings)(?:/|$)#i',
            $decodedPath,
        );
    }
}

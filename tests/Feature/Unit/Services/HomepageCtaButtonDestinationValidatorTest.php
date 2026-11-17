<?php

declare(strict_types=1);

use App\Services\HomepageCta\HomepageCtaButtonDestinationValidator;

it('accepts safe local URLs', function (string $url): void {
    $validator = app(HomepageCtaButtonDestinationValidator::class);

    expect($validator->isValidUrl($url))->toBeTrue();
})->with([
    '/',
    '/layanan',
    '/berita/contoh-berita',
    '/layanan?category=arsip',
]);

it('accepts safe absolute HTTP and HTTPS URLs', function (string $url): void {
    $validator = app(HomepageCtaButtonDestinationValidator::class);

    expect($validator->isValidUrl($url))->toBeTrue();
})->with([
    'https://example.com',
    'http://example.com/layanan',
]);

it('rejects unsafe URLs', function (string $url): void {
    $validator = app(HomepageCtaButtonDestinationValidator::class);

    expect($validator->isValidUrl($url))->toBeFalse();
})->with([
    '',
    '//evil.com',
    'javascript:alert(1)',
    'data:text/html,<script>alert(1)</script>',
    'https://user:password@example.com',
    '/admin',
    '/admin/users',
    '/dashboard',
    '/settings/profile',
    '/%61dmin/users',
    '/foo/../admin',
    'https://',
]);

it('allows only explicitly approved public named routes', function (): void {
    $validator = app(HomepageCtaButtonDestinationValidator::class);

    expect($validator->isAllowedRoute('home'))->toBeTrue()
        ->and($validator->isAllowedRoute('admin.dashboard'))->toBeFalse()
        ->and($validator->isAllowedRoute('login'))->toBeFalse()
        ->and($validator->isAllowedRoute('public.news.show'))->toBeFalse()
        ->and($validator->isAllowedRoute('nonexistent.route'))->toBeFalse();
});

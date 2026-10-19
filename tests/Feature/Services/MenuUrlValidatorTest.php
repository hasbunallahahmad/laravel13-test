<?php

declare(strict_types=1);

use App\Services\Menu\MenuUrlValidator;

it('accepts safe local paths and absolute HTTP URLs for menu destinations', function (): void {
    $validator = new MenuUrlValidator();

    foreach ([
        '/profil',
        '/profil/sejarah?bagian=arsip#koleksi',
        '/berita/slug-berita',
        'http://example.org/profil',
        'https://example.org/layanan',
    ] as $url) {
        expect($validator->isValid($url))->toBeTrue();
    }
});

it('rejects unsafe schemes, credentials, protocol-relative URLs and protected paths', function (): void {
    $validator = new MenuUrlValidator();

    foreach ([
        'javascript:alert(1)',
        'data:text/html,hello',
        'ftp://example.org/file',
        'https://trusted.example@evil.example/path',
        '//evil.example/path',
        '/admin',
        '/admin/dashboard',
        '/ADMIN/users',
        '/dashboard',
        '/settings/profile',
        '/%61dmin/dashboard',
        '/%252561dmin/dashboard',
        '/public/../admin/dashboard',
        '/%2e%2e/admin/dashboard',
        '/\\\\evil.example/path',
    ] as $url) {
        expect($validator->isValid($url))
            ->toBeFalse("Expected URL to be rejected: {$url}");
    }
});

<?php

use App\Services\Content\ContentHtmlSanitizer;

beforeEach(function () {
    $this->sanitizer = app(ContentHtmlSanitizer::class);
});

it('allows supported rich text formatting', function () {
    $html = <<<'HTML'
        <p>Paragraph <strong>bold</strong> <em>italic</em> <u>underline</u> <s>strike</s></p>
        <h2>Heading 2</h2>
        <h3>Heading 3</h3>
        <ul><li>Bullet</li></ul>
        <ol><li>Number</li></ol>
        <blockquote><p>Quote</p></blockquote>
    HTML;

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->toContain('<strong>bold</strong>')
        ->toContain('<em>italic</em>')
        ->toContain('<u>underline</u>')
        ->toContain('<s>strike</s>')
        ->toContain('<h2>Heading 2</h2>')
        ->toContain('<h3>Heading 3</h3>')
        ->toContain('<blockquote>');
});

it('allows safe http and https links', function () {
    $html = <<<'HTML'
        <p>
            <a href="https://example.com">HTTPS</a>
            <a href="http://example.com">HTTP</a>
        </p>
    HTML;

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->toContain('href="https://example.com"')
        ->toContain('href="http://example.com"');
});

it('allows relative links', function () {
    $html = '<a href="/berita/contoh-berita">Berita</a>';

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->toContain('href="/berita/contoh-berita"');
});

it('allows target blank with rel attributes', function () {
    $html = <<<'HTML'
        <a
            href="https://example.com"
            target="_blank"
            rel="noopener noreferrer"
        >
            Example
        </a>
    HTML;

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->toContain('target="_blank"')
        ->toContain('rel="noreferrer noopener"');
});

it('removes javascript links', function () {
    $html = '<a href="javascript:alert(document.domain)">Malicious link</a>';

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->not->toContain('javascript:')
        ->not->toContain('alert(document.domain)');
});

it('removes data links', function () {
    $html = '<a href="data:text/html,<script>alert(1)</script>">Malicious link</a>';

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->not->toContain('data:text/html')
        ->not->toContain('<script>');
});

it('removes vbscript links', function () {
    $html = '<a href="vbscript:msgbox(1)">Malicious link</a>';

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->not->toContain('vbscript:')
        ->not->toContain('msgbox');
});

it('removes event handler attributes', function () {
    $html = <<<'HTML'
        <p onclick="alert(1)" onmouseover="alert(2)">
            Dangerous paragraph
        </p>
    HTML;

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->not->toContain('onclick')
        ->not->toContain('onmouseover')
        ->toContain('Dangerous paragraph');
});

it('allows target blank links with the expected security attributes', function () {
    $html = '<p><a href="https://example.com" target="_blank" rel="noreferrer noopener">Example</a></p>';

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->toContain('href="https://example.com"')
        ->toContain('target="_blank"')
        ->toContain('rel="noreferrer noopener"');
});

it('removes unsupported link targets', function () {
    $html = '<p>
        <a href="https://example.com" target="_parent">Parent</a>
        <a href="https://example.org" target="_top">Top</a>
    </p>';

    $result = $this->sanitizer->sanitize($html);

    expect($result)
        ->not->toContain('target="_parent"')
        ->not->toContain('target="_top"');
});

test('allows safe content images with supported attributes', function () {
    $html = <<<'HTML'
        <p>Konten dengan gambar:</p>
        <img
            src="/storage/media/2026/09/example.webp"
            alt="Logo Dinas Arsip dan Perpustakaan"
            width="800"
            height="600"
        >
    HTML;

    $sanitized = $this->sanitizer->sanitize($html);

    expect($sanitized)
        ->toContain('<img')
        ->toContain('src="/storage/media/2026/09/example.webp"')
        ->toContain('alt="Logo Dinas Arsip dan Perpustakaan"')
        ->toContain('width="800"')
        ->toContain('height="600"');
});

test('removes unsafe image attributes', function () {
    $html = <<<'HTML'
        <img
            src="javascript:alert(1)"
            alt="Unsafe image"
            onclick="alert(1)"
            onerror="alert(1)"
            style="position: fixed; background: url(javascript:alert(1));"
        >
    HTML;

    $sanitized = $this->sanitizer->sanitize($html);

    expect($sanitized)
        ->not->toContain('javascript:')
        ->not->toContain('onclick')
        ->not->toContain('onerror')
        ->not->toContain('position: fixed');
});

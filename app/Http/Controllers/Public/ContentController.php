<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Public;

use App\\Enums\\ContentType;
use App\\Http\\Controllers\\Controller;
use App\\Models\\Content;
use App\\Services\\Content\\ContentHtmlSanitizer;
use Illuminate\\View\\View;

final class ContentController extends Controller
{
    public function __construct(
        private readonly ContentHtmlSanitizer $contentHtmlSanitizer,
    ) {}

    public function news(string $slug): View
    {
        return $this->show($this->findPublishedContent(
            ContentType::NEWS,
            $slug,
        ));
    }

    public function announcement(string $slug): View
    {
        return $this->show($this->findPublishedContent(
            ContentType::ANNOUNCEMENT,
            $slug,
        ));
    }

    public function event(string $slug): View
    {
        return $this->show($this->findPublishedContent(
            ContentType::EVENT,
            $slug,
        ));
    }

    public function article(string $slug): View
    {
        return $this->show($this->findPublishedContent(
            ContentType::ARTICLE,
            $slug,
        ));
    }

    private function findPublishedContent(
        ContentType $type,
        string $slug,
    ): Content {
        return Content::query()
            ->published()
            ->where('type', $type)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function show(Content $content): View
    {
        return view('public.content.show', [
            'content' => $content,
            'sanitizedBody' => $this->contentHtmlSanitizer->sanitize(
                (string) $content->body,
            ),
        ]);
    }
}

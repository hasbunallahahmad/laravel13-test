<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\View\View;

final class ContentController extends Controller
{
    public function news(string $slug): View
    {
        $content = $this->findPublishedContent(
            ContentType::NEWS,
            $slug,
        );

        return view('public.content.show', compact('content'));
    }

    public function announcement(string $slug): View
    {
        $content = $this->findPublishedContent(
            ContentType::ANNOUNCEMENT,
            $slug,
        );

        return view('public.content.show', compact('content'));
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

    public function event(string $slug): View
    {
        $content = $this->findPublishedContent(
            ContentType::EVENT,
            $slug,
        );

        return view('public.content.show', compact('content'));
    }
}

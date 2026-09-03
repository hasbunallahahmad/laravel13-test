<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\StoreContentRequest;
use App\Http\Requests\Admin\Content\UpdateContentRequest;
use App\Models\Content;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContentController extends Controller
{
    /**
     * Display a listing of the contents.
     */
    public function index(): View
    {
        $contents = Content::query()
            ->with('author')
            ->latest()
            ->paginate(15);

        return view('admin.contents.index', [
            'contents' => $contents,
        ]);
    }

    /**
     * Show the form for creating a new content.
     */
    public function create(): View
    {
        $authors = User::query()
            ->select(['uuid', 'name'])
            ->orderBy('name')
            ->get();

        return view('admin.contents.create', [
            'authors' => $authors,
        ]);
    }

    /**
     * Store a newly created content.
     */
    public function store(StoreContentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $authorId = $this->resolveAuthorId($validated['author_uuid'] ?? null);

        unset($validated['author_uuid']);

        $content = new Content($validated);
        $content->author_id = $authorId;
        $content->save();

        return redirect()
            ->route('admin.contents.edit', $content)
            ->with('success', 'Content berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified content.
     */
    public function edit(Content $content): View
    {
        $authors = User::query()
            ->select(['uuid', 'name'])
            ->orderBy('name')
            ->get();

        return view('admin.contents.edit', [
            'content' => $content,
            'authors' => $authors,
        ]);
    }

    /**
     * Update the specified content.
     */
    public function update(
        UpdateContentRequest $request,
        Content $content,
    ): RedirectResponse {
        $validated = $request->validated();

        $authorId = $this->resolveAuthorId($validated['author_uuid'] ?? null);

        unset($validated['author_uuid']);

        $content->fill($validated);
        $content->author_id = $authorId;
        $content->save();

        return redirect()
            ->route('admin.contents.edit', $content)
            ->with('success', 'Content berhasil diperbarui.');
    }

    /**
     * Remove the specified content.
     */
    public function destroy(Content $content): RedirectResponse
    {
        $content->delete();

        return redirect()
            ->route('admin.contents.index')
            ->with('success', 'Content berhasil dihapus.');
    }

    /**
     * Display deleted contents.
     */
    public function trash(): View
    {
        $contents = Content::onlyTrashed()
            ->with('author')
            ->latest('deleted_at')
            ->paginate(15);

        return view('admin.contents.trash', [
            'contents' => $contents,
        ]);
    }

    /**
     * Restore a deleted content.
     */
    public function restore(Content $content)
    {
        $content->restore();

        return redirect()
            ->route('admin.contents.trash')
            ->with('success', 'Content berhasil dipulihkan.');
    }

    /**
     * Permanently delete a content.
     */
    public function forceDestroy(Content $content)
    {
        $content->forceDelete();

        return redirect()
            ->route('admin.contents.trash')
            ->with('success', 'Content berhasil dihapus permanen.');
    }

    /**
     * Resolve author UUID into the internal user ID.
     *
     * The internal database identifier never comes from the request.
     */
    private function resolveAuthorId(?string $authorUuid): ?int
    {
        if ($authorUuid === null || $authorUuid === '') {
            return null;
        }

        return User::query()
            ->where('uuid', $authorUuid)
            ->value('id');
    }
}

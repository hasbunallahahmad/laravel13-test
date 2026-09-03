<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\UpdateMediaRequest;
use App\Http\Requests\Admin\Media\UploadMediaRequest;
use App\Models\Media;
use App\Services\Media\MediaService;
use Illuminate\Http\RedirectResponse;
// use Illuminate\Http\Request;
use Illuminate\View\View;

final class MediaController extends Controller
{
    public function __construct(
        private readonly MediaService $mediaService,
    ) {}

    /**
     * Display the media library.
     */
    public function index(): View
    {
        $media = Media::query()
            ->with('uploader')
            ->latest()
            ->paginate(24);

        return view('admin.media.index', [
            'media' => $media,
        ]);
    }

    /**
     * Store a newly uploaded media file.
     */
    public function store(
        UploadMediaRequest $request,
    ): RedirectResponse {
        $this->mediaService->upload(
            $request->toData(),
        );

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media berhasil diunggah.');
    }

    /**
     * Display a media item.
     */
    public function show(Media $media): View
    {
        return view('admin.media.show', [
            'media' => $media,
        ]);
    }

    /**
     * Update media metadata.
     */
    public function update(
        UpdateMediaRequest $request,
        Media $media,
    ): RedirectResponse {
        $this->mediaService->update(
            $media,
            $request->toData(),
        );

        return redirect()
            ->route('admin.media.show', $media)
            ->with('success', 'Metadata media berhasil diperbarui.');
    }

    /**
     * Soft delete a media item.
     */
    public function destroy(Media $media): RedirectResponse
    {
        $media->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media berhasil dipindahkan ke sampah.');
    }

    /**
     * Restore a soft-deleted media item.
     */
    public function restore(Media $media): RedirectResponse
    {
        $media->restore();

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media berhasil dipulihkan.');
    }

    /**
     * Permanently delete a media item.
     */
    public function forceDestroy(Media $media): RedirectResponse
    {
        $this->mediaService->forceDelete($media);

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media berhasil dihapus permanen.');
    }
}

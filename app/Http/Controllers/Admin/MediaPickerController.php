<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\View\View;

class MediaPickerController extends Controller
{
    public function index(): View
    {
        $media = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest()
            ->get();

        return view('admin.media.picker', compact('media'));
    }
}

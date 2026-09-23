@props(['media'])

<div data-media-picker class="space-y-4">
    @if ($media->isEmpty())
        <div class="rounded-lg border border-dashed border-app-border px-6 py-10 text-center">
            <p class="text-sm text-app-text-muted">
                Belum ada gambar yang tersedia.
            </p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($media as $item)
                @php
                    $variants = $item->metadata['variants'] ?? [];

                    $originalPath = $item->path . '/' . $item->file_name;

                    $thumbnailPath = $variants['thumbnail'] ?? $originalPath;
                    $imagePath = $variants['webp'] ?? $originalPath;

                    $thumbnailUrl = Storage::disk($item->disk)->url($thumbnailPath);
                    $imageUrl = Storage::disk($item->disk)->url($imagePath);

                    $width = $item->metadata['width'] ?? null;
                    $height = $item->metadata['height'] ?? null;

                    $alt = $item->alt_text ?: $item->original_name;
                @endphp

                <button type="button" data-media-picker-item data-media-uuid="{{ $item->uuid }}"
                    data-media-src="{{ $imageUrl }}" data-media-thumbnail="{{ $thumbnailUrl }}"
                    data-media-alt="{{ $alt }}" data-media-width="{{ $width }}"
                    data-media-height="{{ $height }}"
                    class="group block w-full overflow-hidden rounded-xl border border-zinc-200 bg-white text-left transition hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                        <img src="{{ $thumbnailUrl }}" alt="{{ $alt }}" class="h-full w-full object-contain"
                            loading="lazy">
                    </div>

                    <div class="p-3">
                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $item->original_name }}
                        </p>

                        @if ($width && $height)
                            <p class="mt-1 text-xs text-zinc-500">
                                {{ $width }} × {{ $height }} px
                            </p>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</div>

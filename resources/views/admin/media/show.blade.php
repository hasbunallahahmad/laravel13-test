<x-layouts::admin :title="$media->original_name">
    @php
        $isImage = str_starts_with((string) $media->mime_type, 'image/');

        $metadata = is_array($media->metadata) ? $media->metadata : [];

        $variants = $metadata['variants'] ?? [];

        $originalPath = trim($media->path . '/' . $media->file_name, '/');

        $originalUrl = \Illuminate\Support\Facades\Storage::disk($media->disk)->url($originalPath);

        $webpPath = $variants['webp'] ?? null;
        $thumbnailPath = $variants['thumbnail'] ?? null;

        $webpUrl = $webpPath ? \Illuminate\Support\Facades\Storage::disk($media->disk)->url($webpPath) : null;

        $thumbnailUrl = $thumbnailPath
            ? \Illuminate\Support\Facades\Storage::disk($media->disk)->url($thumbnailPath)
            : null;

        $width = $metadata['width'] ?? null;
        $height = $metadata['height'] ?? null;

        $altText = $media->alt_text ?: $media->original_name;
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <a href="{{ route('admin.media.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                    </svg>

                    Kembali ke Media Library
                </a>

                <div class="mt-3">
                    <h1 class="truncate text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                        {{ $media->original_name }}
                    </h1>

                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Detail media
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('admin.media.edit', $media->uuid) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 3.5a2.121 2.121 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z" />
                    </svg>

                    Edit
                </a>

                <form method="POST" action="{{ route('admin.media.destroy', $media->uuid) }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-3.5 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-900/60 dark:bg-zinc-900 dark:text-red-400 dark:hover:bg-red-950/40 dark:focus:ring-red-400 dark:focus:ring-offset-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" class="size-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7l1 13h10l1-13" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V4h6v3" />
                        </svg>

                        Hapus
                    </button>
                </form>
            </div>
        </div>

        {{-- Main --}}
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">

            {{-- Preview --}}
            <section
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">
                        Preview
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Pratinjau media yang tersimpan.
                    </p>
                </div>

                <div class="p-5">
                    @if ($isImage)
                        <div
                            class="flex min-h-[360px] items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-800 dark:bg-zinc-950">
                            <img src="{{ $webpUrl ?: $originalUrl }}" alt="{{ $altText }}"
                                @if ($width) width="{{ $width }}" @endif
                                @if ($height) height="{{ $height }}" @endif
                                class="max-h-[560px] max-w-full object-contain">
                        </div>
                    @else
                        <div
                            class="flex min-h-[360px] items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950">
                            <div class="text-center">
                                <div
                                    class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-white text-zinc-500 shadow-sm dark:bg-zinc-900 dark:text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.6" class="size-8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14 2.75H6.5A1.75 1.75 0 0 0 4.75 4.5v15A1.75 1.75 0 0 0 6.5 21.25h11a1.75 1.75 0 0 0 1.75-1.75V8Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2.75V8h5.25" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8M8 15.5h5" />
                                    </svg>
                                </div>

                                <p class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ strtoupper($media->extension ?: 'FILE') }}
                                </p>

                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $media->mime_type }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Information --}}
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">
                        Informasi Media
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Informasi teknis file.
                    </p>
                </div>

                <dl class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    <div class="px-5 py-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            UUID
                        </dt>

                        <dd class="mt-1 break-all text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $media->uuid }}
                        </dd>
                    </div>

                    <div class="px-5 py-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            Nama File
                        </dt>

                        <dd class="mt-1 break-all text-sm text-zinc-900 dark:text-zinc-100"
                            title="{{ $media->file_name }}">
                            {{ $media->file_name }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-2 divide-x divide-zinc-100 dark:divide-zinc-800">
                        <div class="px-5 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                MIME
                            </dt>

                            <dd class="mt-1 break-all text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $media->mime_type }}
                            </dd>
                        </div>

                        <div class="px-5 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                Ekstensi
                            </dt>

                            <dd class="mt-1 text-sm uppercase text-zinc-900 dark:text-zinc-100">
                                {{ $media->extension ?: '-' }}
                            </dd>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 divide-x divide-zinc-100 dark:divide-zinc-800">
                        <div class="px-5 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                Ukuran
                            </dt>

                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                <span>
                                    {{ number_format($media->size / 1024, 1, ',', '.') }} KB
                                </span>

                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    ({{ $media->size }} bytes)
                                </span>
                            </dd>
                        </div>

                        <div class="px-5 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                Disk
                            </dt>

                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $media->disk }}
                            </dd>
                        </div>
                    </div>

                    @if ($width && $height)
                        <div class="px-5 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                Dimensi
                            </dt>

                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $width }} × {{ $height }} px
                            </dd>
                        </div>
                    @endif

                    <div class="px-5 py-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            Uploader
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $media->uploader?->name ?? '-' }}
                        </dd>
                    </div>

                    <div class="px-5 py-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            Path
                        </dt>

                        <dd class="mt-1 break-all font-mono text-xs text-zinc-600 dark:text-zinc-400">
                            {{ $originalPath }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Variants --}}
        @if ($isImage && ($webpPath || $thumbnailPath))
            <section
                class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">
                        Image Variants
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        File turunan yang dibuat oleh sistem untuk optimasi website.
                    </p>
                </div>

                <div class="grid gap-4 p-5 sm:grid-cols-2">

                    @if ($webpPath)
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        WebP
                                    </p>

                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        Optimized image
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-brand-100 px-2.5 py-1 text-xs font-medium text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                                    WEBP
                                </span>
                            </div>

                            <p class="mt-3 break-all font-mono text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $webpPath }}
                            </p>

                            <a href="{{ $webpUrl }}" target="_blank" rel="noopener noreferrer"
                                class="mt-3 inline-flex text-xs font-medium text-brand-700 hover:underline dark:text-brand-300">
                                Buka WebP
                            </a>
                        </div>
                    @endif

                    @if ($thumbnailPath)
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        Thumbnail
                                    </p>

                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        Preview untuk Media Library
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    THUMB
                                </span>
                            </div>

                            <p class="mt-3 break-all font-mono text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $thumbnailPath }}
                            </p>

                            <a href="{{ $thumbnailUrl }}" target="_blank" rel="noopener noreferrer"
                                class="mt-3 inline-flex text-xs font-medium text-brand-700 hover:underline dark:text-brand-300">
                                Buka Thumbnail
                            </a>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Accessibility Metadata --}}
        <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">
                    Informasi Konten
                </h2>

                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Metadata yang digunakan pada konten website.
                </p>
            </div>

            <dl class="grid gap-5 p-5 lg:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Alt Text
                    </dt>

                    <dd
                        class="mt-2 rounded-lg bg-zinc-50 px-3 py-2.5 text-sm text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
                        {{ $media->alt_text ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Caption
                    </dt>

                    <dd
                        class="mt-2 whitespace-pre-line rounded-lg bg-zinc-50 px-3 py-2.5 text-sm text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
                        {{ $media->caption ?: '-' }}
                    </dd>
                </div>
            </dl>
        </section>

        {{-- Footer Navigation --}}
        <div>
            <a href="{{ route('admin.media.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-950">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                </svg>

                Kembali
            </a>
        </div>
    </div>
</x-layouts::admin>

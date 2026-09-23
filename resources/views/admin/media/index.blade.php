<x-layouts::admin :title="__('Media')">
    <div class="space-y-8">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-8 items-center justify-center rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" class="size-4" aria-hidden="true">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21" />
                        </svg>
                    </span>

                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                        Media Library
                    </h1>
                </div>

                <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                    Kelola gambar, dokumen, dan media yang digunakan dalam website.
                </p>
            </div>
        </div>

        {{-- Upload --}}
        @if (auth()->user()->can('media.create'))
            <section id="media-upload" class="media-upload-panel scroll-mt-24" aria-labelledby="media-upload-title">

                <div class="media-upload-header">
                    <div>
                        <h2 id="media-upload-title" class="text-sm font-semibold text-zinc-900 dark:text-white">
                            Upload media
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Pilih file dari perangkat Anda untuk menambahkannya ke Media Library.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
                    data-media-upload-form class="space-y-5">

                    @csrf

                    {{-- File Dropzone --}}
                    <div>
                        <label for="file" class="media-upload-dropzone">

                            {{-- Empty State --}}
                            <span data-media-upload-empty class="media-upload-empty">

                                {{-- Image Upload Illustration --}}
                                <span class="media-upload-empty-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none"
                                        class="size-20">

                                        <rect x="8" y="8" width="48" height="48" rx="16"
                                            class="fill-brand-100 dark:fill-brand-900/50" />

                                        <rect x="19" y="19" width="27" height="23" rx="4"
                                            stroke="currentColor" stroke-width="2.5"
                                            class="text-brand-700 dark:text-brand-300" />

                                        <path
                                            d="M21.5 38L28.5 30.5C29.3 29.65 30.65 29.65 31.45 30.5L35 34.25L37.5 31.5C38.3 30.6 39.7 30.6 40.5 31.5L44 35.5V39.5H21.5V38Z"
                                            fill="currentColor" class="text-brand-500 dark:text-brand-400" />

                                        <circle cx="39.5" cy="25.5" r="3" fill="currentColor"
                                            class="text-brand-600 dark:text-brand-300" />

                                        <circle cx="45" cy="44" r="8" fill="currentColor"
                                            class="text-brand-600 dark:text-brand-400" />

                                        <path d="M45 48V40M41.5 43.5L45 40L48.5 43.5" stroke="white" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>

                                <span class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                    Pilih file untuk diupload
                                </span>

                                <span class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    JPG, PNG, WebP, atau PDF · maksimal 10 MB
                                </span>

                                <span class="media-upload-select-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 16V4m0 0-4 4m4-4 4 4" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5 14v4.5A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5V14" />
                                    </svg>

                                    Pilih File
                                </span>
                            </span>

                            {{-- Selected File Preview --}}
                            <span data-media-upload-preview class="media-upload-preview hidden">

                                <span class="media-upload-preview-frame">

                                    <img data-media-upload-preview-image class="media-upload-preview-image hidden"
                                        src="" alt="">

                                    <span data-media-upload-preview-icon class="media-upload-preview-icon hidden"
                                        aria-hidden="true">

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.6" class="size-14">

                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14 2.75H6.5A1.75 1.75 0 0 0 4.75 4.5v15A1.75 1.75 0 0 0 6.5 21.25h11a1.75 1.75 0 0 0 1.75-1.75V8Z"
                                                class="text-zinc-400 dark:text-zinc-500" />

                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2.75V8h5.25"
                                                class="text-zinc-400 dark:text-zinc-500" />

                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8M8 15.5h5"
                                                class="text-zinc-500 dark:text-zinc-400" />
                                        </svg>
                                    </span>
                                </span>

                                <span class="media-upload-preview-info">
                                    <span data-media-upload-preview-name class="media-upload-preview-name"></span>

                                    <span data-media-upload-preview-meta class="media-upload-preview-meta"></span>
                                </span>

                                <span class="media-upload-preview-hint">
                                    Klik untuk mengganti file
                                </span>
                            </span>

                            <input id="file" name="file" type="file"
                                accept="image/jpeg,image/png,image/webp,application/pdf" required
                                class="media-upload-input" />
                        </label>

                        @error('file')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Metadata --}}
                    <div class="grid gap-5 lg:grid-cols-2">
                        <div>
                            <label for="alt_text" class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                Alt Text
                            </label>

                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Deskripsi singkat untuk aksesibilitas gambar.
                            </p>

                            <input id="alt_text" name="alt_text" type="text" value="{{ old('alt_text') }}"
                                maxlength="255"
                                class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500">

                            @error('alt_text')
                                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="caption" class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                Caption
                            </label>

                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Keterangan yang ditampilkan bersama media jika diperlukan.
                            </p>

                            <textarea id="caption" name="caption" rows="3" maxlength="5000"
                                class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500">{{ old('caption') }}</textarea>

                            @error('caption')
                                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end border-t border-zinc-200 px-5 pt-5 pb-5 dark:border-zinc-800">

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                            </svg>

                            Upload Media
                        </button>
                    </div>
                </form>
            </section>
        @endif

        {{-- Library Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                    Koleksi Media
                </h2>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Media terbaru yang tersedia di website.
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Media Trash --}}
                <a href="{{ route('admin.media.trash') }}" aria-label="Buka Media Sampah"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.29 0c-.34.059-.68.114-1.022.165m14.334 0a48.11 48.11 0 0 1-3.478-.397m-10.856 0a48.11 48.11 0 0 1-3.478.397m14.334 0L17.25 3.75m-10.5 0L5.25 5.79m0 0h13.5" />
                    </svg>

                    <span>Media Sampah</span>
                </a>

                @if ($media->total())
                    <span
                        class="hidden rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 sm:inline-flex dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $media->total() }} media
                    </span>
                @endif
            </div>
        </div>

        {{-- Media Library --}}
        @if ($media->count())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($media as $item)
                    <article
                        class="group overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">

                        {{-- Preview --}}
                        <a href="{{ route('admin.media.show', $item) }}" class="block">
                            <div class="relative aspect-4/3 overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                @if (str_starts_with((string) $item->mime_type, 'image/'))
                                    @php
                                        $previewPath = $item->thumbnail_path ?: $item->storage_path;
                                    @endphp

                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk($item->disk)->url($previewPath) }}"
                                        alt="{{ $item->alt_text ?: $item->original_name }}"
                                        width="{{ data_get($item->metadata, 'width') }}"
                                        height="{{ data_get($item->metadata, 'height') }}" loading="lazy"
                                        class="size-full object-contain p-4 transition duration-300 group-hover:scale-105">
                                @else
                                    <div
                                        class="flex size-full items-center justify-center bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">

                                        <div class="text-center">
                                            <div
                                                class="mx-auto flex size-12 items-center justify-center rounded-xl bg-white text-zinc-500 shadow-sm dark:bg-zinc-900 dark:text-zinc-400">

                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="1.6"
                                                    class="size-6" aria-hidden="true">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14 2.75H6.5A1.75 1.75 0 0 0 4.75 4.5v15A1.75 1.75 0 0 0 6.5 21.25h11a1.75 1.75 0 0 0 1.75-1.75V8Z" />

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14 2.75V8h5.25M8 12h8M8 15.5h5" />
                                                </svg>
                                            </div>

                                            <div class="mt-3 text-xs font-semibold uppercase tracking-wide">
                                                {{ $item->extension ?: 'file' }}
                                            </div>

                                            <div class="mt-1 text-xs">
                                                Dokumen
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </a>

                        {{-- Information --}}
                        <div class="space-y-3 p-4">
                            <div class="min-w-0">
                                <a href="{{ route('admin.media.show', $item) }}"
                                    class="block truncate text-sm font-semibold text-zinc-900 transition hover:text-zinc-600 dark:text-white dark:hover:text-zinc-300"
                                    title="{{ $item->original_name }}">
                                    {{ $item->original_name }}
                                </a>

                                <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $item->mime_type }}
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-between gap-3 border-t border-zinc-100 pt-3 text-xs text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">

                                <span class="uppercase">
                                    {{ $item->extension }}
                                </span>

                                <span>
                                    {{ number_format($item->size / 1024, 1) }} KB
                                </span>
                            </div>

                            @if ($item->uploader)
                                <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span
                                        class="flex size-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.7" class="size-3.5"
                                            aria-hidden="true">

                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 21a7.5 7.5 0 0 1 15 0" />
                                        </svg>
                                    </span>

                                    <span class="truncate">
                                        {{ $item->uploader->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($media->hasPages())
                <div
                    class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                    {{ $media->links() }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div
                class="rounded-xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">

                <div
                    class="mx-auto flex size-14 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" class="size-7" aria-hidden="true">

                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m3 16 5.5-5.5a2.12 2.12 0 0 1 3 0L17 16m-4-4 1.5-1.5a2.12 2.12 0 0 1 3 0L21 13" />

                        <rect width="18" height="18" x="3" y="3" rx="2" />
                    </svg>
                </div>

                <h2 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">
                    Belum ada media
                </h2>

                <p class="mx-auto mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
                    Belum ada file yang tersedia di Media Library.
                </p>

                @if (auth()->user()->can('media.create'))
                    <a href="#media-upload"
                        class="mt-5 inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">
                        Tambahkan Media
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-layouts::admin>

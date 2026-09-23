<x-layouts::admin :title="__('Edit Media')">
    @php
        $isImage = str_starts_with((string) $media->mime_type, 'image/');

        $metadata = is_array($media->metadata) ? $media->metadata : [];

        $variants = $metadata['variants'] ?? [];

        $originalPath = trim($media->path . '/' . $media->file_name, '/');

        $originalUrl = \Illuminate\Support\Facades\Storage::disk($media->disk)->url($originalPath);

        $webpPath = $variants['webp'] ?? null;

        $previewUrl = $webpPath
            ? \Illuminate\Support\Facades\Storage::disk($media->disk)->url($webpPath)
            : $originalUrl;

        $width = $metadata['width'] ?? null;
        $height = $metadata['height'] ?? null;

        $altText = $media->alt_text ?: $media->original_name;
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <a href="{{ route('admin.media.show', $media->uuid) }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                </svg>

                Kembali ke Detail Media
            </a>

            <div class="mt-3">
                <h1 class="truncate text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                    Edit Media
                </h1>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Perbarui informasi metadata media tanpa mengubah file.
                </p>
            </div>
        </div>

        {{-- Main --}}
        <div class="grid gap-6 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">

            {{-- Media Preview --}}
            <section
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">
                        Media
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Media yang sedang diperbarui.
                    </p>
                </div>

                <div class="p-5">
                    @if ($isImage)
                        <div
                            class="flex min-h-[280px] items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950">
                            <img src="{{ $previewUrl }}" alt="{{ $altText }}"
                                @if ($width) width="{{ $width }}" @endif
                                @if ($height) height="{{ $height }}" @endif
                                class="max-h-[420px] max-w-full object-contain">
                        </div>
                    @else
                        <div
                            class="flex min-h-[280px] items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950">
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

                    <div class="mt-4">
                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-white"
                            title="{{ $media->original_name }}">
                            {{ $media->original_name }}
                        </p>

                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            @if ($width && $height)
                                {{ $width }} × {{ $height }} px
                                ·
                            @endif

                            {{ number_format($media->size / 1024, 1, ',', '.') }} KB
                        </p>
                    </div>
                </div>
            </section>

            {{-- Metadata Form --}}
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">
                        Metadata
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Perbarui informasi yang digunakan oleh website.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.media.update', $media->uuid) }}" class="space-y-6 p-5">
                    @csrf
                    @method('PATCH')

                    {{-- Alt Text --}}
                    <div>
                        <label for="alt_text" class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">
                            Alt Text
                        </label>

                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Deskripsi singkat untuk aksesibilitas gambar.
                        </p>

                        <input id="alt_text" name="alt_text" type="text"
                            value="{{ old('alt_text', $media->alt_text) }}" maxlength="255"
                            class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500">

                        @error('alt_text')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Caption --}}
                    <div>
                        <label for="caption" class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">
                            Caption
                        </label>

                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Keterangan yang ditampilkan bersama media jika diperlukan.
                        </p>

                        <textarea id="caption" name="caption" rows="6" maxlength="5000"
                            class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500">{{ old('caption', $media->caption) }}</textarea>

                        @error('caption')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div
                        class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:justify-end dark:border-zinc-800">
                        <a href="{{ route('admin.media.show', $media->uuid) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">
                            Batal
                        </a>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9.5 17 19 7.5" />
                            </svg>

                            Simpan
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-layouts::admin>

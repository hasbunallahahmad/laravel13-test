<x-layouts::admin :title="__('Media Sampah')">
    <div class="space-y-8">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7" class="size-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M9.75 10.5v6m4.5-6v6M5.25 6.75l.75 13.5a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5l.75-13.5M9 6.75V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5v2.25" />
                        </svg>
                    </span>

                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                        Media Sampah
                    </h1>
                </div>

                <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                    Media yang dihapus sementara dan masih dapat dipulihkan atau dihapus secara permanen.
                </p>
            </div>

            {{-- Back to Library --}}
            <a href="{{ route('admin.media.index') }}"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6" />
                </svg>

                Kembali ke Media
            </a>
        </div>

        {{-- Trash Header --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                    Media Terhapus
                </h2>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    File berikut berada di tempat sampah dan tidak tampil di Media Library.
                </p>
            </div>

            @if ($media->total())
                <span
                    class="hidden rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 sm:inline-flex dark:bg-zinc-800 dark:text-zinc-300">
                    {{ $media->total() }} media
                </span>
            @endif
        </div>

        {{-- Trash Library --}}
        @if ($media->count())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($media as $item)
                    <article
                        class="group overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">

                        {{-- Preview --}}
                        <div class="relative aspect-4/3 overflow-hidden bg-zinc-100 dark:bg-zinc-800">

                            @if (str_starts_with((string) $item->mime_type, 'image/'))
                                @php
                                    $previewPath = $item->thumbnail_path ?: $item->storage_path;
                                @endphp

                                <img src="{{ \Illuminate\Support\Facades\Storage::disk($item->disk)->url($previewPath) }}"
                                    alt="{{ $item->alt_text ?: $item->original_name }}"
                                    width="{{ data_get($item->metadata, 'width') }}"
                                    height="{{ data_get($item->metadata, 'height') }}" loading="lazy"
                                    class="size-full object-contain p-4 opacity-75 transition duration-300 group-hover:opacity-100 group-hover:scale-105">
                            @else
                                <div
                                    class="flex size-full items-center justify-center bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">

                                    <div class="text-center">
                                        <div
                                            class="mx-auto flex size-12 items-center justify-center rounded-xl bg-white text-zinc-500 shadow-sm dark:bg-zinc-900 dark:text-zinc-400">

                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.6" class="size-6"
                                                aria-hidden="true">

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

                            {{-- Deleted Badge --}}
                            <div class="absolute left-3 top-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-zinc-900/80 px-2.5 py-1 text-[11px] font-medium text-white shadow-sm backdrop-blur-sm dark:bg-white/90 dark:text-zinc-900">

                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" class="size-3" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 6.75h16.5M9.75 10.5v6m4.5-6v6M5.25 6.75l.75 13.5a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5l.75-13.5" />
                                    </svg>

                                    Terhapus
                                </span>
                            </div>
                        </div>

                        {{-- Information --}}
                        <div class="space-y-3 p-4">
                            <div class="min-w-0">
                                <p class="block truncate text-sm font-semibold text-zinc-900 dark:text-white"
                                    title="{{ $item->original_name }}">
                                    {{ $item->original_name }}
                                </p>

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

                            {{-- Deleted At --}}
                            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">

                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.7" class="size-3.5" aria-hidden="true">

                                        <circle cx="12" cy="12" r="8.25" />
                                        <path stroke-linecap="round" d="M12 7.5v5l3 1.75" />
                                    </svg>
                                </span>

                                <span>
                                    Dihapus {{ $item->deleted_at?->format('d M Y, H:i') }}
                                </span>
                            </div>

                            {{-- Actions --}}
                            @if (auth()->user()->can('media.restore') || auth()->user()->can('media.force-delete'))
                                <div
                                    class="grid gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800 sm:grid-cols-2">

                                    @can('media.restore')
                                        <form method="POST" action="{{ route('admin.media.restore', $item) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">

                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8" class="size-3.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 14.25 5.25 10.5 9 6.75" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5.25 10.5H14a5.25 5.25 0 1 1-5.25 5.25" />
                                                </svg>

                                                Pulihkan
                                            </button>
                                        </form>
                                    @endcan

                                    @can('media.force-delete')
                                        <form method="POST" action="{{ route('admin.media.force-delete', $item) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-950/50 dark:focus:ring-red-400 dark:focus:ring-offset-zinc-900">

                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="1.8"
                                                    class="size-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m9 9 6 6m0-6-6 6" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.5 6.75h15M9 6.75V4.5h6v2.25m-8.25 0 .75 13.5h9l.75-13.5" />
                                                </svg>

                                                Hapus Permanen
                                            </button>
                                        </form>
                                    @endcan

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
                            d="M3.75 6.75h16.5M9.75 10.5v6m4.5-6v6M5.25 6.75l.75 13.5a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5l.75-13.5" />
                    </svg>
                </div>

                <h2 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">
                    Tempat sampah kosong
                </h2>

                <p class="mx-auto mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
                    Tidak ada media yang sedang berada di tempat sampah.
                </p>

                <a href="{{ route('admin.media.index') }}"
                    class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-brand-400 dark:focus:ring-offset-zinc-900">

                    Kembali ke Media
                </a>
            </div>
        @endif
    </div>
</x-layouts::admin>

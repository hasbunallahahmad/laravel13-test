<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.">
    <title>Dinas Arsip dan Perpustakaan Kota Semarang</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-5">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">
                Pemerintah Kota Semarang
            </p>
            <h1 class="mt-1 text-2xl font-bold">
                Dinas Arsip dan Perpustakaan
            </h1>
        </div>
    </header>

    <nav aria-label="Navigasi utama" class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap gap-6 px-6 py-4">
            @foreach ($menus as $menu)
                <div class="flex flex-col gap-3">
                    @if ($menu->public_url !== null)
                        <a href="{{ $menu->public_url }}"
                            @if ($menu->target === '_blank') target="_blank" rel="noopener noreferrer" @endif
                            class="font-medium text-zinc-700 hover:text-emerald-700">
                            {{ $menu->label }}
                        </a>
                    @else
                        <span class="font-medium text-zinc-700">
                            {{ $menu->label }}
                        </span>
                    @endif

                    @if ($menu->children->isNotEmpty())
                        <div class="ml-4 flex flex-wrap gap-4">
                            @foreach ($menu->children as $child)
                                @if ($child->public_url !== null)
                                    <a href="{{ $child->public_url }}"
                                        @if ($child->target === '_blank') target="_blank" rel="noopener noreferrer" @endif
                                        class="text-sm text-zinc-600 hover:text-emerald-700">
                                        {{ $child->label }}
                                    </a>
                                @else
                                    <span class="text-sm text-zinc-600">
                                        {{ $child->label }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-10">
        <section aria-labelledby="news-heading">
            <div class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">
                    Informasi terkini
                </p>
                <h2 id="news-heading" class="mt-2 text-3xl font-bold">
                    Berita Terbaru
                </h2>
            </div>

            @forelse ($news as $item)
                <article class="mb-4 rounded-xl border border-zinc-200 bg-white p-6">
                    <h3 class="text-xl font-semibold">
                        <a href="{{ route('public.news.show', ['slug' => $item->slug]) }}"
                            class="hover:text-emerald-700">
                            {{ $item->title }}
                        </a>
                    </h3>

                    @if ($item->excerpt)
                        <p class="mt-3 leading-7 text-zinc-600">
                            {{ $item->excerpt }}
                        </p>
                    @endif

                    @if ($item->published_at)
                        <p class="mt-4 text-sm text-zinc-500">
                            {{ $item->published_at->translatedFormat('d F Y') }}
                        </p>
                    @endif
                </article>
            @empty
                <p class="rounded-xl border border-zinc-200 bg-white p-6 text-zinc-600">
                    Belum ada berita yang tersedia.
                </p>
            @endforelse
        </section>
    </main>

    @if ($settings->isNotEmpty())
        <section aria-labelledby="public-settings-heading" class="mx-auto mt-12 max-w-7xl px-6 pb-10">
            <h2 id="public-settings-heading" class="text-2xl font-bold">
                Informasi Publik
            </h2>

            <dl class="mt-4 space-y-3">
                @foreach ($settings as $setting)
                    <div>
                        <dt class="font-medium text-zinc-700">
                            {{ $setting->key }}
                        </dt>
                        <dd class="text-zinc-600">
                            {{ $setting->value }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    <footer class="mt-12 border-t border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-6 text-sm text-zinc-500">
            Dinas Arsip dan Perpustakaan Kota Semarang
        </div>
    </footer>
</body>

</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <meta property="og:title" content="@yield('og_title', 'Dinas Arsip dan Perpustakaan Kota Semarang')">
    <meta property="og:description" content="@yield('meta_description', 'Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">

    <meta name="description" content="@yield('meta_description', 'Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.')">

    <title>@yield('title', 'Dinas Arsip dan Perpustakaan Kota Semarang')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-5">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">
                Pemerintah Kota Semarang
            </p>

            <a href="{{ url('/') }}" class="mt-1 inline-block text-2xl font-bold">
                Dinas Arsip dan Perpustakaan
            </a>
        </div>
    </header>

    <nav aria-label="Navigasi utama" class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap gap-6 px-6 py-4">
            @foreach ($menus as $menu)
                <div class="flex flex-col gap-3">
                    @if ($menu->public_url !== null && $menu->target === '_blank')
                        <a href="{{ $menu->public_url }}" target="_blank" rel="noopener noreferrer" class="font-medium text-zinc-700 hover:text-emerald-700">
                            {{ $menu->label }}
                        </a>
                    @elseif ($menu->public_url !== null)
                        <a href="{{ $menu->public_url }}" class="font-medium text-zinc-700 hover:text-emerald-700">
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
                                @if ($child->public_url !== null && $child->target === '_blank')
                                    <a href="{{ $child->public_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-zinc-600 hover:text-emerald-700">
                                        {{ $child->label }}
                                    </a>
                                @elseif ($child->public_url !== null)
                                    <a href="{{ $child->public_url }}" class="text-sm text-zinc-600 hover:text-emerald-700">
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
        @yield('content')
    </main>

    <footer class="mt-12 border-t border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-6 text-sm text-zinc-500">
            Dinas Arsip dan Perpustakaan Kota Semarang
        </div>
    </footer>
</body>

</html>

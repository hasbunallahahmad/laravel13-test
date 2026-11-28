<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="@yield('meta_description', 'Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.')">

    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <meta property="og:title" content="@yield('og_title', 'Dinas Arsip dan Perpustakaan Kota Semarang')">

    <meta property="og:description" content="@yield('meta_description', 'Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.')">

    <meta property="og:type" content="@yield('og_type', 'website')">

    <meta property="og:url" content="@yield('canonical_url', url()->current())">

    <title>
        @yield('title', 'Dinas Arsip dan Perpustakaan Kota Semarang')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="public-site">

    {{-- Skip link untuk aksesibilitas --}}
    <a href="#main-content" class="public-skip-link">
        Lewati ke konten utama
    </a>

    {{-- =====================================================
         TOP BAR
         ===================================================== --}}
    <div class="public-topbar">
        <div class="public-container public-topbar-inner">

            <div class="public-government">
                <span class="public-government-mark">
                    Pemkot
                </span>

                <span>
                    Pemerintah Kota Semarang
                </span>
            </div>

            <div class="public-topbar-links">
                <a href="#main-content">
                    Aksesibilitas
                </a>

                <span aria-hidden="true">|</span>

                <span>
                    Bahasa Indonesia
                </span>
            </div>

        </div>
    </div>


    {{-- =====================================================
         HEADER
         ===================================================== --}}
    <header class="public-header">

        <div class="public-container public-header-inner">

            {{-- Brand --}}
            <a href="{{ url('/') }}" class="public-brand" aria-label="Beranda Dinas Arsip dan Perpustakaan Kota Semarang">

                <div class="public-brand-logo" aria-hidden="true">
                </div>

                <div class="public-brand-text">
                    <span class="public-brand-government">
                        Pemerintah Kota Semarang
                    </span>

                    <span class="public-brand-name">
                        Dinas Arsip dan Perpustakaan
                    </span>

                    <span class="public-brand-city">
                        Kota Semarang
                    </span>
                </div>

            </a>


            {{-- =================================================
                 DESKTOP NAVIGATION
                 ================================================= --}}
            <nav class="public-navigation" aria-label="Navigasi utama">

                @foreach ($menus as $menu)
                    @if ($menu->children->isNotEmpty())
                        <div class="public-nav-group">

                            @if ($menu->public_url !== null)
                                <a href="{{ $menu->public_url }}" @if ($menu->target === '_blank') target="_blank" rel="noopener noreferrer" @endif class="public-nav-link">
                                    {{ $menu->label }}

                                    <span class="public-nav-chevron" aria-hidden="true">
                                        ▾
                                    </span>
                                </a>
                            @else
                                <span class="public-nav-link">
                                    {{ $menu->label }}

                                    <span class="public-nav-chevron" aria-hidden="true">
                                        ▾
                                    </span>
                                </span>
                            @endif


                            <div class="public-dropdown">

                                @foreach ($menu->children as $child)
                                    @if ($child->public_url !== null)
                                        <a href="{{ $child->public_url }}" <a href="{{ $child->public_url }}" @if ($child->target === '_blank') target="_blank" rel="noopener noreferrer" @endif
                                            class="public-dropdown-link">
                                            {{ $child->label }}
                                        </a>
                                    @else
                                        <span class="public-dropdown-link">
                                            {{ $child->label }}
                                        </span>
                                    @endif
                                @endforeach

                            </div>

                        </div>
                    @else
                        @if ($menu->public_url !== null)
                            <a href="{{ $menu->public_url }}" @if ($menu->target === '_blank') target="_blank" rel="noopener noreferrer" @endif class="public-nav-link">
                                {{ $menu->label }}
                            </a>
                        @else
                            <span class="public-nav-link">
                                {{ $menu->label }}
                            </span>
                        @endif
                    @endif
                @endforeach

            </nav>


            {{-- Mobile menu button --}}
            <button type="button" class="public-mobile-menu-button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="public-mobile-navigation" data-public-menu-toggle>
                <span></span>
                <span></span>
                <span></span>
            </button>

        </div>


        {{-- =====================================================
             MOBILE NAVIGATION
             ===================================================== --}}
        <nav id="public-mobile-navigation" class="public-mobile-navigation" aria-label="Navigasi mobile" hidden>

            <div class="public-container public-mobile-navigation-inner">

                @foreach ($menus as $menu)
                    <div class="public-mobile-menu-item">

                        @if ($menu->public_url !== null)
                            <a href="{{ $menu->public_url }}" @if ($menu->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                {{ $menu->label }}
                            </a>
                        @else
                            <span>
                                {{ $menu->label }}
                            </span>
                        @endif


                        @if ($menu->children->isNotEmpty())
                            <div class="public-mobile-children">

                                @foreach ($menu->children as $child)
                                    @if ($child->public_url !== null)
                                        <a href="{{ $child->public_url }}" @if ($child->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                            {{ $child->label }}
                                        </a>
                                    @else
                                        <span>
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

    </header>


    {{-- =====================================================
         MAIN CONTENT
         ===================================================== --}}
    <main id="main-content" class="public-main">

        @yield('content')

    </main>


    {{-- =====================================================
         FOOTER
         ===================================================== --}}
    <footer class="public-footer">

        <div class="public-container">

            <div class="public-footer-grid">

                {{-- Identity --}}
                <div class="public-footer-brand">

                    <div class="public-footer-logo">
                        D
                    </div>

                    <div>
                        <p class="public-footer-title">
                            Dinas Arsip dan Perpustakaan
                        </p>

                        <p class="public-footer-subtitle">
                            Kota Semarang
                        </p>
                    </div>

                </div>


                {{-- Informasi --}}
                <div>

                    <h2 class="public-footer-heading">
                        Informasi
                    </h2>

                    <ul class="public-footer-list">

                        <li>
                            <a href="{{ url('/') }}">
                                Beranda
                            </a>
                        </li>

                        <li>
                            <a href="#main-content">
                                Pusat Informasi
                            </a>
                        </li>

                    </ul>

                </div>


                {{-- Kontak --}}
                <div>

                    <h2 class="public-footer-heading">
                        Kontak
                    </h2>

                    <div class="public-footer-contact">

                        <p>
                            Dinas Arsip dan Perpustakaan
                            Kota Semarang
                        </p>

                        <p>
                            Pemerintah Kota Semarang
                        </p>

                    </div>

                </div>

            </div>


            <div class="public-footer-bottom">

                <p>
                    &copy; {{ date('Y') }}
                    Dinas Arsip dan Perpustakaan Kota Semarang.
                    Seluruh hak cipta dilindungi.
                </p>

                <p>
                    Situs resmi Pemerintah Kota Semarang
                </p>

            </div>

        </div>

    </footer>


    {{-- =====================================================
         PUBLIC JAVASCRIPT
         ===================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector(
                '[data-public-menu-toggle]'
            );

            const navigation = document.getElementById(
                'public-mobile-navigation'
            );

            if (!toggle || !navigation) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isOpen =
                    toggle.getAttribute('aria-expanded') === 'true';

                toggle.setAttribute(
                    'aria-expanded',
                    String(!isOpen)
                );

                navigation.hidden = isOpen;

                toggle.setAttribute(
                    'aria-label',
                    isOpen ?
                    'Buka menu navigasi' :
                    'Tutup menu navigasi'
                );
            });
        });
    </script>

    @stack('scripts')

</body>

</html>

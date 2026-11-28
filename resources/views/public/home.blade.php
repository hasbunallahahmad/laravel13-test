@extends('layouts.public')

@section('title', 'Dinas Arsip dan Perpustakaan Kota Semarang')

@section('meta_description', 'Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.')

@section('content')

    {{-- =====================================================
         HERO
         ===================================================== --}}
    <section class="public-home-hero">

        <div class="public-container public-home-hero-inner">

            <div class="public-home-hero-content">

                <p class="public-eyebrow">
                    Pemerintah Kota Semarang
                </p>

                <h1 id="home-hero-heading" class="public-home-hero-title">
                    {{ $settings->get('appearance.home_hero_title')?->value ?? 'Dinas Arsip...' }}
                </h1>

                <p id="home-hero-subtitle" class="public-home-hero-description">
                    {{ $settings->get('appearance.home_hero_subtitle')?->value ?? 'Menjaga...' }}
                </p>


                <div class="public-home-hero-actions">

                    <a href="#layanan-utama" class="public-button public-button-primary">
                        <span>Jelajahi Layanan</span>

                        <span aria-hidden="true">
                            →
                        </span>
                    </a>

                    <a href="#informasi" class="public-button public-button-secondary">
                        Profil Dinas
                    </a>

                </div>

            </div>


            {{-- Visual hero sementara --}}
            <div class="public-home-hero-visual" aria-hidden="true">

                <div class="public-hero-building">
                    <div class="public-hero-building-top"></div>

                    <div class="public-hero-building-body">

                        <div class="public-hero-building-window"></div>
                        <div class="public-hero-building-window"></div>
                        <div class="public-hero-building-window"></div>

                        <div class="public-hero-building-window"></div>
                        <div class="public-hero-building-window"></div>
                        <div class="public-hero-building-window"></div>

                        <div class="public-hero-building-window"></div>
                        <div class="public-hero-building-window"></div>
                        <div class="public-hero-building-window"></div>

                    </div>

                </div>

                <div class="public-hero-circle public-hero-circle-one"></div>
                <div class="public-hero-circle public-hero-circle-two"></div>

            </div>

        </div>

    </section>


    {{-- =====================================================
         QUICK ACCESS
         ===================================================== --}}
    <section id="layanan-utama" class="public-quick-access" aria-labelledby="quick-access-heading">

        <div class="public-container">

            <div class="public-quick-grid">

                <a href="#" class="public-quick-card">

                    <span class="public-quick-icon">
                        📁
                    </span>

                    <span class="public-quick-content">
                        <strong>
                            Layanan Informasi
                        </strong>

                        <small>
                            Akses informasi dengan mudah
                        </small>
                    </span>

                    <span class="public-quick-arrow">
                        →
                    </span>

                </a>


                <a href="#" class="public-quick-card">

                    <span class="public-quick-icon">
                        📄
                    </span>

                    <span class="public-quick-content">
                        <strong>
                            Arsip
                        </strong>

                        <small>
                            Informasi dan layanan kearsipan
                        </small>
                    </span>

                    <span class="public-quick-arrow">
                        →
                    </span>

                </a>


                <a href="#" class="public-quick-card">

                    <span class="public-quick-icon">
                        📚
                    </span>

                    <span class="public-quick-content">
                        <strong>
                            Perpustakaan
                        </strong>

                        <small>
                            Baca, belajar, dan berkembang
                        </small>
                    </span>

                    <span class="public-quick-arrow">
                        →
                    </span>

                </a>


                <a href="#" class="public-quick-card">

                    <span class="public-quick-icon">
                        📅
                    </span>

                    <span class="public-quick-content">
                        <strong>
                            Agenda
                        </strong>

                        <small>
                            Ikuti kegiatan terbaru
                        </small>
                    </span>

                    <span class="public-quick-arrow">
                        →
                    </span>

                </a>

            </div>

        </div>

    </section>


    {{-- =====================================================
         BERITA
         ===================================================== --}}
    <section class="public-section public-news-section" aria-labelledby="news-heading">

        <div class="public-container">

            <div class="public-section-heading">

                <div>

                    <p class="public-eyebrow">
                        Informasi terkini
                    </p>

                    <h2 id="news-heading" class="public-section-title">
                        Berita Terbaru
                    </h2>

                </div>

                <a href="#" class="public-section-link">
                    Lihat semua
                    <span aria-hidden="true">→</span>
                </a>

            </div>


            @if ($news->isNotEmpty())

                <div class="public-news-grid">

                    @foreach ($news as $item)
                        <article class="public-news-card">

                            <div class="public-news-image">

                                <div class="public-news-image-placeholder" aria-hidden="true">
                                    <span>Berita</span>
                                </div>

                            </div>


                            <div class="public-news-body">

                                @if ($item->published_at)
                                    <time datetime="{{ $item->published_at->toIso8601String() }}" class="public-news-date">
                                        {{ $item->published_at->translatedFormat('d F Y') }}
                                    </time>
                                @endif


                                <h3 class="public-news-title">

                                    <a href="{{ route('public.news.show', ['slug' => $item->slug]) }}">
                                        {{ $item->title }}
                                    </a>

                                </h3>


                                @if ($item->excerpt)
                                    <p class="public-news-excerpt">
                                        {{ $item->excerpt }}
                                    </p>
                                @endif

                            </div>

                        </article>
                    @endforeach

                </div>
            @else
                <div class="public-empty-state">
                    Belum ada berita yang tersedia.
                </div>

            @endif

        </div>

    </section>

    {{-- =====================================================
     AGENDA
     ===================================================== --}}
    <section class="public-section public-agenda-section" aria-labelledby="agenda-heading">

        <div class="public-container">

            <div class="public-section-heading">

                <div>

                    <p class="public-eyebrow">
                        Kegiatan
                    </p>

                    <h2 id="agenda-heading" class="public-section-title">
                        Agenda Mendatang
                    </h2>

                </div>

                <a href="#" class="public-section-link">
                    Lihat semua
                    <span aria-hidden="true">→</span>
                </a>

            </div>


            @if ($events->isNotEmpty())

                <div class="public-agenda-list">

                    @foreach ($events as $event)
                        <article class="public-agenda-card">

                            <div class="public-agenda-date">

                                @if ($event->published_at)
                                    <span class="public-agenda-day">
                                        {{ $event->published_at->format('d') }}
                                    </span>

                                    <span class="public-agenda-month">
                                        {{ strtoupper($event->published_at->translatedFormat('M')) }}
                                    </span>
                                @else
                                    <span class="public-agenda-day">
                                        —
                                    </span>

                                    <span class="public-agenda-month">
                                        —
                                    </span>
                                @endif

                            </div>


                            <div class="public-agenda-content">

                                <h3>

                                    <a href="{{ route('public.event.show', ['slug' => $event->slug]) }}">
                                        {{ $event->title }}
                                    </a>

                                </h3>

                                @if ($event->excerpt)
                                    <p>
                                        {{ $event->excerpt }}
                                    </p>
                                @endif

                            </div>


                            <a href="{{ route('public.event.show', ['slug' => $event->slug]) }}" class="public-agenda-arrow" aria-label="Lihat {{ $event->title }}">
                                →
                            </a>

                        </article>
                    @endforeach

                </div>
            @else
                <div class="public-empty-state">
                    Belum ada agenda yang tersedia.
                </div>

            @endif

        </div>

    </section>

    {{-- =====================================================
         INFORMASI + PENGUMUMAN
         ===================================================== --}}
    <section id="informasi" class="public-section public-information-section" aria-labelledby="information-heading">

        <div class="public-container">

            <div class="public-information-grid">

                {{-- Informasi Publik --}}
                @if ($settings->contains(fn($setting) => str_starts_with($setting->key, 'public_')))
                    <div class="public-information-panel">

                        <div class="public-section-heading">

                            <div>

                                <p class="public-eyebrow">
                                    Transparansi
                                </p>

                                <h2 id="information-heading" class="public-section-title">
                                    Informasi Publik
                                </h2>

                            </div>

                        </div>


                        @if ($settings->isNotEmpty())

                            <div class="public-information-list">

                                @foreach ($settings->filter(fn($setting) => str_starts_with($setting->key, 'public_'))->take(4) as $setting)
                                    <div class="public-information-item">

                                        <div>

                                            <span class="public-information-key">
                                                {{ str_replace('_', ' ', $setting->key) }}
                                            </span>

                                            <span class="public-information-value">
                                                {{ $setting->value }}
                                            </span>

                                        </div>

                                        <span aria-hidden="true">
                                            →
                                        </span>

                                    </div>
                                @endforeach

                            </div>
                        @else
                            <div class="public-empty-state">
                                Informasi publik belum tersedia.
                            </div>

                        @endif

                    </div>
                @endif


                {{-- Pengumuman --}}
                <div class="public-information-panel">

                    <div class="public-section-heading">

                        <div>

                            <p class="public-eyebrow">
                                Pemberitahuan
                            </p>

                            <h2 class="public-section-title">
                                Pengumuman
                            </h2>

                        </div>

                        <a href="#" class="public-section-link">
                            Lihat semua
                            <span aria-hidden="true">→</span>
                        </a>

                    </div>


                    <div class="public-announcement-list">

                        <div class="public-announcement-item">

                            <span class="public-announcement-icon">
                                !
                            </span>

                            <div>

                                <h3>
                                    Informasi layanan
                                </h3>

                                <p>
                                    Informasi layanan terbaru akan
                                    ditampilkan di sini.
                                </p>

                            </div>

                        </div>


                        <div class="public-announcement-item">

                            <span class="public-announcement-icon">
                                !
                            </span>

                            <div>

                                <h3>
                                    Pengumuman publik
                                </h3>

                                <p>
                                    Pantau informasi resmi dari
                                    Dinas Arsip dan Perpustakaan.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- =====================================================
         CTA
         ===================================================== --}}
    {{-- =========================================================
     HOMEPAGE CTA
========================================================= --}}

    @if ($homepageCta)
        <section class="public-section public-cta-section" aria-labelledby="homepage-cta-heading">
            <div class="public-container">
                <div class="public-cta-content">

                    <h2 id="homepage-cta-heading" class="public-cta-title">
                        {{ $homepageCta->title }}
                    </h2>

                    @if ($homepageCta->description)
                        <p class="public-cta-description">
                            {{ $homepageCta->description }}
                        </p>
                    @endif

                </div>
            </div>
        </section>
    @endif

@endsection

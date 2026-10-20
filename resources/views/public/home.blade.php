@extends('layouts.public')

@section('content')
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
                    <a href="{{ route('public.news.show', ['slug' => $item->slug]) }}" class="hover:text-emerald-700">
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

    @if ($settings->isNotEmpty())
        <section aria-labelledby="public-settings-heading" class="mt-12">
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
    </section>
@endsection

@extends('layouts.public')

@section('og_title', $content->title)
@section('canonical_url', url()->current())
@section('og_type', 'article')

@section('title', $content->title . ' — Dinas Arsip dan Perpustakaan Kota Semarang')

@php
    $metaDescription = filled($content->excerpt) ? $content->excerpt : 'Website resmi Dinas Arsip dan Perpustakaan Kota Semarang.';
@endphp

@section('meta_description', $metaDescription)

@section('content')
    <article class="rounded-xl border border-zinc-200 bg-white p-6 sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">
            Informasi Publik
        </p>

        <h1 class="mt-2 text-3xl font-bold">
            {{ $content->title }}
        </h1>

        @if ($content->excerpt)
            <p class="mt-4 leading-7 text-zinc-600">
                {{ $content->excerpt }}
            </p>
        @endif

        <div class="mt-8">
            {!! $sanitizedBody !!}
        </div>
    </article>
@endsection

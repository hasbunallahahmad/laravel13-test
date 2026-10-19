<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $content->title }}</title>
</head>

<body>
    <main>
        <article>
            <h1>{{ $content->title }}</h1>

            @if ($content->excerpt)
                <p>{{ $content->excerpt }}</p>
            @endif

            <div>
                {!! $sanitizedBody !!}
            </div>
        </article>
    </main>
</body>

</html>

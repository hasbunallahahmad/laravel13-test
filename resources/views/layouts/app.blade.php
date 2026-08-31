<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot ?? '' }}
    </flux:main>
</x-layouts::app.sidebar>

{{-- <x-layouts::app.sidebar :title="$title ?? null">

    <div>
        APP LAYOUT TEST
    </div>

    {{ $slot ?? '' }}

</x-layouts::app.sidebar> --}}

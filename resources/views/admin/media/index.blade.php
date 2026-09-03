<x-layouts::app>
    <div>
        <h1>Media Library</h1>

        @foreach ($media as $item)
            <div>
                {{ $item->original_name }}
            </div>
        @endforeach
    </div>
</x-layouts::app>

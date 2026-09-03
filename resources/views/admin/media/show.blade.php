<x-layouts::app>
    <div>
        <h1>{{ $media->original_name }}</h1>

        <p>Media UUID: {{ $media->uuid }}</p>
        <p>File: {{ $media->file_name }}</p>
        <p>Type: {{ $media->mime_type }}</p>
        <p>Size: {{ $media->size }}</p>
    </div>
</x-layouts::app>

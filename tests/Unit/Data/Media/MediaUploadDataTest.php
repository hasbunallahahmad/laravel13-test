<?php

declare(strict_types=1);

use App\Data\Media\MediaUploadData;
use Illuminate\Http\UploadedFile;

test('media upload data stores upload information', function () {
    $file = UploadedFile::fake()->image(
        'photo.jpg',
        800,
        600,
    );

    $data = new MediaUploadData(
        file: $file,
        altText: 'Foto kegiatan',
        caption: 'Kegiatan Dinas Arpus',
        uploadedBy: 123,
    );

    expect($data->file)
        ->toBe($file)

        ->and($data->altText)
        ->toBe('Foto kegiatan')

        ->and($data->caption)
        ->toBe('Kegiatan Dinas Arpus')

        ->and($data->uploadedBy)
        ->toBe(123);
});

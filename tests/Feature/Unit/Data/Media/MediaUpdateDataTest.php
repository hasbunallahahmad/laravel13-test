<?php

declare(strict_types=1);

use App\Data\Media\MediaUpdateData;

test('media update data stores alt text and caption', function () {
    $data = new MediaUpdateData(
        altText: 'Foto kegiatan',
        caption: 'Dokumentasi kegiatan Dinas Arsip dan Perpustakaan.',
    );

    expect($data->altText)
        ->toBe('Foto kegiatan')
        ->and($data->caption)
        ->toBe('Dokumentasi kegiatan Dinas Arsip dan Perpustakaan.');
});

test('media update data allows nullable fields', function () {
    $data = new MediaUpdateData(
        altText: null,
        caption: null,
    );

    expect($data->altText)
        ->toBeNull()
        ->and($data->caption)
        ->toBeNull();
});

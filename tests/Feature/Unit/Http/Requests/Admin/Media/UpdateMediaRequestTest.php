<?php

declare(strict_types=1);

use App\Data\Media\MediaUpdateData;
use App\Http\Requests\Admin\Media\UpdateMediaRequest;
use Illuminate\Support\Facades\Validator;

function validateUpdateMediaRequest(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new UpdateMediaRequest();

    return Validator::make(
        $data,
        $request->rules(),
    );
}

test('alt text is optional', function () {
    $validator = validateUpdateMediaRequest([
        'caption' => 'Keterangan media',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('caption is optional', function () {
    $validator = validateUpdateMediaRequest([
        'alt_text' => 'Foto kegiatan',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('alt text accepts a valid string', function () {
    $validator = validateUpdateMediaRequest([
        'alt_text' => 'Foto kegiatan',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('caption accepts a valid string', function () {
    $validator = validateUpdateMediaRequest([
        'caption' => 'Dokumentasi kegiatan Dinas Arsip dan Perpustakaan.',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('alt text cannot exceed 255 characters', function () {
    $validator = validateUpdateMediaRequest([
        'alt_text' => str_repeat('a', 256),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('caption cannot exceed 5000 characters', function () {
    $validator = validateUpdateMediaRequest([
        'caption' => str_repeat('a', 5001),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('alt text must be a string', function () {
    $validator = validateUpdateMediaRequest([
        'alt_text' => ['invalid'],
    ]);

    expect($validator->fails())->toBeTrue();
});

test('caption must be a string', function () {
    $validator = validateUpdateMediaRequest([
        'caption' => ['invalid'],
    ]);

    expect($validator->fails())->toBeTrue();
});

test('request converts validated data to media update data', function () {
    $request = UpdateMediaRequest::create(
        '/admin/media/test',
        'PUT',
        [
            'alt_text' => 'Foto kegiatan',
            'caption' => 'Dokumentasi kegiatan Dinas Arsip dan Perpustakaan.',
        ],
    );

    $request->setContainer(app());

    $request->validateResolved();

    $data = $request->toData();

    expect($data)
        ->toBeInstanceOf(MediaUpdateData::class)
        ->and($data->altText)
        ->toBe('Foto kegiatan')
        ->and($data->caption)
        ->toBe('Dokumentasi kegiatan Dinas Arsip dan Perpustakaan.');
});

test('request converts nullable fields to media update data', function () {
    $request = UpdateMediaRequest::create(
        '/admin/media/test',
        'PUT',
        [],
    );

    $request->setContainer(app());

    $request->validateResolved();

    $data = $request->toData();

    expect($data)
        ->toBeInstanceOf(MediaUpdateData::class)
        ->and($data->altText)
        ->toBeNull()
        ->and($data->caption)
        ->toBeNull();
});

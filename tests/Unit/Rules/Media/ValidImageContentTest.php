<?php

declare(strict_types=1);

use App\Rules\Media\ValidImageContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

uses(Tests\TestCase::class);

it('rejects an image file whose content cannot be decoded', function () {
    $file = UploadedFile::fake()->create(
        'fake-image.jpg',
        10,
        'image/jpeg',
    );

    $validator = Validator::make(
        ['file' => $file],
        [
            'file' => [
                new ValidImageContent(),
            ],
        ],
    );

    expect($validator->fails())
        ->toBeTrue();

    expect($validator->errors()->has('file'))
        ->toBeTrue();
});

it('skips validation for non-image files', function () {
    $file = UploadedFile::fake()->create(
        'dokumen.pdf',
        100,
        'application/pdf',
    );

    $validator = Validator::make(
        ['file' => $file],
        [
            'file' => [
                new ValidImageContent(),
            ],
        ],
    );

    expect($validator->fails())
        ->toBeFalse();
});

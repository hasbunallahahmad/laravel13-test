<?php

declare(strict_types=1);

use App\Http\Requests\Admin\Media\UploadMediaRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

function validateUploadMediaRequest(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new UploadMediaRequest();

    return Validator::make(
        $data,
        $request->rules(),
        $request->messages(),
        $request->attributes(),
    );
}

/*
|--------------------------------------------------------------------------
| File Validation
|--------------------------------------------------------------------------
*/

test('file is required', function () {
    $validator = validateUploadMediaRequest([]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('file'))->toBeTrue();
});

test('accepts jpg image', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('accepts jpeg image', function () {
    $file = UploadedFile::fake()->image('photo.jpeg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('accepts png image', function () {
    $file = UploadedFile::fake()->image('photo.png');

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('accepts webp image', function () {
    $file = UploadedFile::fake()->image('photo.webp');

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('accepts pdf file', function () {
    $file = UploadedFile::fake()->create(
        'document.pdf',
        100,
        'application/pdf',
    );

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('rejects unsupported file types', function () {
    $file = UploadedFile::fake()->create(
        'document.docx',
        100,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    );

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('file'))->toBeTrue();
});

test('rejects php files', function () {
    $file = UploadedFile::fake()->create(
        'shell.php',
        10,
        'application/x-php',
    );

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('file'))->toBeTrue();
});

test('rejects phtml files', function () {
    $file = UploadedFile::fake()->create(
        'shell.phtml',
        10,
        'text/html',
    );

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('file'))->toBeTrue();
});

test('rejects files larger than 10 mb', function () {
    $file = UploadedFile::fake()->create(
        'large-image.jpg',
        10241,
        'image/jpeg',
    );

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('file'))->toBeTrue();
});

// test('rejects jpg file with invalid content', function () {
//     $file = UploadedFile::fake()->createWithContent(
//         'fake-image.jpg',
//         'this is not a real jpeg image',
//     );

//     $validator = validateUploadMediaRequest([
//         'file' => $file,
//     ]);

//     expect($validator->fails())->toBeTrue()
//         ->and($validator->errors()->has('file'))->toBeTrue();
// });

// test('rejects pdf file with invalid content', function () {
//     $file = UploadedFile::fake()->createWithContent(
//         'fake-document.pdf',
//         'this is not a real pdf document',
//     );

//     $validator = validateUploadMediaRequest([
//         'file' => $file,
//     ]);

//     expect($validator->fails())->toBeTrue()
//         ->and($validator->errors()->has('file'))->toBeTrue();
// });

/*
|--------------------------------------------------------------------------
| alt_text Validation
|--------------------------------------------------------------------------
*/

test('alt text is optional', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('alt text must be a string', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
        'alt_text' => 12345,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('alt_text'))->toBeTrue();
});

test('alt text cannot exceed 255 characters', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
        'alt_text' => str_repeat('a', 256),
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('alt_text'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| caption Validation
|--------------------------------------------------------------------------
*/

test('caption is optional', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('caption must be a string', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
        'caption' => 12345,
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('caption'))->toBeTrue();
});

test('caption cannot exceed 5000 characters', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $validator = validateUploadMediaRequest([
        'file' => $file,
        'caption' => str_repeat('a', 5001),
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('caption'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| DTO Transformation
|--------------------------------------------------------------------------
*/

test('request transforms validated data into media upload data', function () {
    $file = UploadedFile::fake()->image(
        'photo.jpg',
        800,
        600,
    );
    $request = UploadMediaRequest::create(
        '/admin/media',
        'POST',
        [
            'alt_text' => 'Foto kegiatan',
            'caption' => 'Kegiatan Dinas Arpus',
        ],
    );

    $request->files->set('file', $file);

    $validator = \Illuminate\Support\Facades\Validator::make(
        $request->all(),
        $request->rules(),
        $request->messages(),
        $request->attributes(),
    );

    expect($validator->passes())->toBeTrue();

    $request->setValidator($validator);

    $data = $request->toData();

    expect($data->file)
        ->toBe($file)

        ->and($data->altText)
        ->toBe('Foto kegiatan')

        ->and($data->caption)
        ->toBe('Kegiatan Dinas Arpus');
});

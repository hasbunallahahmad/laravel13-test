<?php

declare(strict_types=1);

use App\Enums\ContentType;

it('contains the public content types required by the website', function () {
    expect(ContentType::cases())
        ->toEqualCanonicalizing([
            ContentType::PAGE,
            ContentType::NEWS,
            ContentType::ANNOUNCEMENT,
            ContentType::EVENT,
            ContentType::ARTICLE,
            ContentType::SERVICE,
            ContentType::DOCUMENT,
            ContentType::PUBLICATION,
            ContentType::FAQ,
        ]);
});

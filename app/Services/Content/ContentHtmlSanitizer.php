<?php

namespace App\Services\Content;

use Mews\Purifier\Facades\Purifier;

class ContentHtmlSanitizer
{
    public function sanitize(string $html): string
    {
        return Purifier::clean($html, 'default');
    }
}

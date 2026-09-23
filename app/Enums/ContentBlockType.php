<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentBlockType: string
{
    case HERO = 'hero';
    case TEXT = 'text';
    case IMAGE = 'image';
    case GALLERY = 'gallery';
    case CTA = 'cta';
}

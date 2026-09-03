<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentType: string
{
    case PAGE = 'page';
    case ARTICLE = 'article';
    case NEWS = 'news';
    case ANNOUNCEMENT = 'announcement';
}

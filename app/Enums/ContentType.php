<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentType: string
{
    case PAGE = 'page';
    case NEWS = 'news';
    case ANNOUNCEMENT = 'announcement';
    case EVENT = 'event';
    case ARTICLE = 'article';
    case SERVICE = 'service';
    case DOCUMENT = 'document';
    case PUBLICATION = 'publication';
    case FAQ = 'faq';
}

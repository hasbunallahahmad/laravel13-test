<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentStatus: string
{
    case DRAFT = 'draft';
    case REVIEW = 'review';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}

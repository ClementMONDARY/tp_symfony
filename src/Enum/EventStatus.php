<?php

namespace App\Enum;

enum EventStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ONGOING = 'ongoing';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ONGOING => 'Ongoing',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
        };
    }
}


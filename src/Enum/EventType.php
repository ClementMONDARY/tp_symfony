<?php

namespace App\Enum;

enum EventType: string
{
    case WORKSHOP = 'workshop';
    case MEETUP = 'meetup';
    case CONFERENCE = 'conference';

    public function getLabel(): string
    {
        return match ($this) {
            self::WORKSHOP => 'Workshop',
            self::MEETUP => 'Meetup',
            self::CONFERENCE => 'Conference',
        };
    }
}

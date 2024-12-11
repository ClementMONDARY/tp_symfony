<?php

namespace App\DBAL\Type;

use App\Enum\EventStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EventStatusType extends Type
{
    public const NAME = 'event_status';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return !empty($value) ? EventStatus::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value instanceof EventStatus ? $value->value : $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

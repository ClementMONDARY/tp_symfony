<?php

namespace App\DBAL\Types;

use App\Enum\EventType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EventTypeType extends Type
{
    public const NAME = 'event_type';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getDoctrineTypeMapping('string');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?EventType
    {
        return $value !== null ? EventType::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof EventType ? $value->value : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}

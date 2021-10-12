<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * @deprecated since 3.5, will be removed in 4.0. Use EasyDoctrine instead.
 */
final class JsonbType extends JsonType
{
    /**
     * @var string
     */
    public const TYPE_NAME = 'JSONB';

    public function getName(): string
    {
        return static::TYPE_NAME;
    }

    /**
     * @param mixed[] $fieldDeclaration
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getDoctrineTypeMapping(static::TYPE_NAME);
    }
}

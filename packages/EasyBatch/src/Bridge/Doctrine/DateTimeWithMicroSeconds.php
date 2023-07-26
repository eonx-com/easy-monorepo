<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class DateTimeWithMicroSeconds extends Type
{
    public const NAME = 'datetime_with_microseconds';

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param mixed[] $column
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return match ($platform->getName()) {
            'mysql' => 'DATETIME(6)',
            'postgresql' => 'TIMESTAMP(6) WITHOUT TIME ZONE',
            default => $platform->getDateTimeTypeDeclarationSQL($column),
        };
    }
}

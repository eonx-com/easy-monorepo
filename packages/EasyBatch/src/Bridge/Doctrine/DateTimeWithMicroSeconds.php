<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class DateTimeWithMicroSeconds extends Type
{
    /**
     * @var string
     */
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
        switch ($platform->getName()) {
            case 'mysql':
                return 'DATETIME(6)';
            case 'postgresql':
                return 'TIMESTAMP(6) WITHOUT TIME ZONE';
            default:
                return $platform->getDateTimeTypeDeclarationSQL($column);
        }
    }
}

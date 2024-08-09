<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;

final class DateTimeWithMicroSecondsType extends Type
{
    public const NAME = 'datetime_with_microseconds';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return 'TIMESTAMP(6) WITHOUT TIME ZONE';
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            return 'DATETIME(6)';
        }

        return $platform->getDateTimeTypeDeclarationSQL($column);
    }
}

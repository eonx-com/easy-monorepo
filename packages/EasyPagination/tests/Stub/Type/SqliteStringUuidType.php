<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stub\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

/**
 * We can't use `\Symfony\Bridge\Doctrine\Types\UuidType` because it creates a BLOB column,
 * which is not supported in `WHERE IN (...)` clauses in SQLite.
 */
final class SqliteStringUuidType extends Type
{
    public const NAME = 'sqlite_string_uuid';

    /**
     * @param \Symfony\Component\Uid\AbstractUid $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        return (string)$value;
    }

    /**
     * @param \Symfony\Component\Uid\AbstractUid|string|null $value
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?AbstractUid
    {
        if ($value instanceof AbstractUid || $value === null) {
            return $value;
        }

        return Uuid::fromString($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 36;
        $column['fixed'] = true;

        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

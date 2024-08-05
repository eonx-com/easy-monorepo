<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Enum;

trait EnumTrait
{
    /**
     * @return array<string>
     */
    public static function caseValues(): array
    {
        return self::extractValues(static::cases());
    }

    /**
     * @param array<self> $cases
     *
     * @return array<string>
     */
    public static function extractValues(array $cases): array
    {
        return \array_map(static fn (self $enum) => $enum->value, $cases);
    }

    public static function hasCase(mixed $case): bool
    {
        return static::tryFrom($case) !== null;
    }
}

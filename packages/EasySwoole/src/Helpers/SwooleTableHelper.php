<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasySwoole\ValueObjects\SwooleTableColumnDefinition;
use OpenSwoole\Table as OpenSwooleTable;
use Swoole\Table as SwooleTable;
use UnexpectedValueException;

final class SwooleTableHelper
{
    private static ?string $tableClass = null;

    /**
     * @param \EonX\EasySwoole\ValueObjects\SwooleTableColumnDefinition[] $columnDefinitions
     */
    public static function create(int $size, array $columnDefinitions): OpenSwooleTable|SwooleTable
    {
        $tableClass = self::getTableClass();
        /** @var \OpenSwoole\Table|\Swoole\Table $table */
        $table = new $tableClass($size);

        foreach ($columnDefinitions as $columnDefinition) {
            if ($columnDefinition instanceof SwooleTableColumnDefinition === false) {
                throw new UnexpectedValueException(\sprintf(
                    'Column definition must be instance of "%s"',
                    SwooleTableColumnDefinition::class,
                ));
            }

            $table->column(
                $columnDefinition->name,
                $columnDefinition->type->value,
                $columnDefinition->size ?? 0
            );
        }

        $table->create();

        return $table;
    }

    public static function getTableClass(): string
    {
        return self::$tableClass ??= \class_exists(OpenSwooleTable::class)
            ? OpenSwooleTable::class
            : SwooleTable::class;
    }
}

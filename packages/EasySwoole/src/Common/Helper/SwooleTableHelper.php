<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Helper;

use EonX\EasySwoole\Common\ValueObject\SwooleTableColumnDefinition;
use OpenSwoole\Table as OpenSwooleTable;
use Swoole\Table as SwooleTable;
use UnexpectedValueException;

final class SwooleTableHelper
{
    /**
     * @var class-string<\OpenSwoole\Table|\Swoole\Table>|null
     */
    private static ?string $tableClass = null;

    /**
     * @param \EonX\EasySwoole\Common\ValueObject\SwooleTableColumnDefinition[] $columnDefinitions
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

    /**
     * @return class-string<\OpenSwoole\Table|\Swoole\Table>
     */
    public static function getTableClass(): string
    {
        return self::$tableClass ??= \class_exists(OpenSwooleTable::class)
            ? OpenSwooleTable::class
            : SwooleTable::class;
    }
}

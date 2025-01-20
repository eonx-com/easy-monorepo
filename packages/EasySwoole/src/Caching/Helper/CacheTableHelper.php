<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Caching\Helper;

use EonX\EasySwoole\Caching\Enum\CacheTableColumn;
use EonX\EasySwoole\Common\Enum\SwooleTableColumnType;
use EonX\EasySwoole\Common\Helper\SwooleTableHelper;
use EonX\EasySwoole\Common\ValueObject\SwooleTableColumnDefinition;
use EonX\EasySwoole\Logging\Helper\OutputHelper;
use OpenSwoole\Table as OpenSwooleTable;
use Swoole\Table as SwooleTable;
use UnexpectedValueException;

final class CacheTableHelper
{
    private const DEFAULT_TABLE_SIZE = 50;

    private const DEFAULT_VALUE_COLUMN_SIZE = 10000;

    private const KEY_COLUMN_SIZE = 'column_size';

    private const KEY_TABLE_SIZE = 'table_size';

    private const SERVER_TABLE_NAMES = 'easy_swoole_cache_table_names';

    private const SERVER_TABLE_NAME_PATTERN = 'easy_swoole_cache_table_%s';

    private const SERVER_TICK_COUNT_TABLE_NAME = 'easy_swoole_tick_count_table';

    private const TICK_COUNT_COLUMN_CURRENT = 'current';

    private const TICK_COUNT_COLUMN_MAXIMUM = 'maximum';

    public static function createCacheTables(array $config, int $cacheClearAfterTickCount): void
    {
        $tables = [];

        foreach ($config as $name => $sizes) {
            // Support simple table name to use default sizes
            if (\is_int($name) && (\is_string($sizes) && $sizes !== '')) {
                $name = $sizes;
                $sizes = [];
            }

            if (\is_array($sizes) === false) {
                throw new UnexpectedValueException(\sprintf(
                    'Cache table "%s" sizes must be an array, "%s" given',
                    $name,
                    \gettype($sizes)
                ));
            }

            if (\in_array($name, $tables, true)) {
                throw new UnexpectedValueException(\sprintf('Cache table "%s" already exists', $name));
            }

            $table = SwooleTableHelper::create(
                size: $sizes[0] ?? $sizes[self::KEY_TABLE_SIZE] ?? self::DEFAULT_TABLE_SIZE,
                columnDefinitions: [
                    new SwooleTableColumnDefinition(CacheTableColumn::Expiry, SwooleTableColumnType::Int),
                    new SwooleTableColumnDefinition(
                        CacheTableColumn::Value,
                        SwooleTableColumnType::String,
                        $sizes[1] ?? $sizes[self::KEY_COLUMN_SIZE] ?? self::DEFAULT_VALUE_COLUMN_SIZE
                    ),
                ]
            );

            $tables[] = $name;
            $_SERVER[self::getServerTableName($name)] = $table;
        }

        // Create table to count ticks
        $tickCountTable = SwooleTableHelper::create(
            size: 2,
            columnDefinitions: [
                new SwooleTableColumnDefinition(CacheTableColumn::Value, SwooleTableColumnType::Int),
            ]
        );
        $tickCountTable->set(self::TICK_COUNT_COLUMN_CURRENT, [CacheTableColumn::Value->value => 0]);
        $tickCountTable->set(
            self::TICK_COUNT_COLUMN_MAXIMUM,
            [CacheTableColumn::Value->value => $cacheClearAfterTickCount]
        );

        $_SERVER[self::SERVER_TICK_COUNT_TABLE_NAME] = $tickCountTable;
        $_SERVER[self::SERVER_TABLE_NAMES] = $tables;

        OutputHelper::writeln('Create following cache tables:');

        foreach ($tables as $table) {
            OutputHelper::writeln(\sprintf('- %s', $table));
        }

        OutputHelper::writeln(\sprintf(
            'Will automatically remove expired records after %d requests',
            $cacheClearAfterTickCount
        ));
    }

    public static function exists(string $name): bool
    {
        return self::get($name) !== null;
    }

    public static function get(
        string $name,
    ): SwooleTable|OpenSwooleTable|null {
        $tableClass = SwooleTableHelper::getTableClass();
        $table = $_SERVER[self::getServerTableName($name)] ?? null;

        return $table instanceof $tableClass ? $table : null;
    }

    public static function tick(): void
    {
        // Remove expired rows to prevent memory leaks
        $tableClass = SwooleTableHelper::getTableClass();
        /** @var \OpenSwoole\Table|\Swoole\Table|null $tickCountTable */
        $tickCountTable = $_SERVER[self::SERVER_TICK_COUNT_TABLE_NAME] ?? null;

        if ($tickCountTable instanceof $tableClass === false) {
            return;
        }

        $tickCountTable->incr(self::TICK_COUNT_COLUMN_CURRENT, CacheTableColumn::Value->value);

        $currentTickCount = $tickCountTable->get(self::TICK_COUNT_COLUMN_CURRENT, CacheTableColumn::Value->value);
        $maximumTickCount = $tickCountTable->get(self::TICK_COUNT_COLUMN_MAXIMUM, CacheTableColumn::Value->value);

        if ($currentTickCount >= $maximumTickCount) {
            $now = \time();

            foreach (($_SERVER[self::SERVER_TABLE_NAMES] ?? []) as $name) {
                $table = self::get($name);

                if ($table === null) {
                    continue;
                }

                /** @var string $key */
                /** @var array $row */
                foreach ($table as $key => $row) {
                    if ($now >= $row[CacheTableColumn::Expiry->value]) {
                        $table->del($key);
                    }
                }
            }
        }

        $tickCountTable->set(self::TICK_COUNT_COLUMN_CURRENT, [CacheTableColumn::Value->value => 0]);
    }

    private static function getServerTableName(string $name): string
    {
        return \sprintf(self::SERVER_TABLE_NAME_PATTERN, $name);
    }
}

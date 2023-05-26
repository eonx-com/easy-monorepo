<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use OpenSwoole\Table as OpenSwooleTable;
use Swoole\Table as SwooleTable;

final class CacheTableHelper
{
    public const COLUMN_EXPIRY = 'expiry';

    public const COLUMN_VALUE = 'value';

    public const KEY_COLUMN_SIZE = 'column_size';

    public const KEY_TABLE_SIZE = 'table_size';

    private const DEFAULT_REQUEST_COUNT_FLUSH = 10000;

    private const DEFAULT_TABLE_SIZE = 50;

    private const DEFAULT_VALUE_COLUMN_SIZE = 10000;

    private const REQUEST_COUNT_COLUMN = '_count';

    private const SERVER_TABLE_NAME_PATTERN = 'easy_swoole_cache_table_%s';

    private const SERVER_TABLE_NAMES = 'easy_swoole_cache_table_names';

    private const SERVER_REQUEST_COUNT_TABLE_NAME = 'easy_swoole_request_count_table';

    private static ?string $tableClass = null;

    /**
     * @param mixed[] $config
     */
    public static function createCacheTables(array $config): void
    {
        $tableClass = self::getTableClass();
        $tables = [];

        foreach ($config as $name => $sizes) {
            // Support simple table name to use default sizes
            if (\is_int($name) && (\is_string($sizes) && $sizes !== '')) {
                $name = $sizes;
                $sizes = [];
            }

            if (\is_array($sizes) === false) {
                throw new \InvalidArgumentException(\sprintf(
                    'Cache table "%s" sizes must be an array, "%s" given',
                    $name,
                    \gettype($sizes)
                ));
            }

            if (\in_array($name, $tables, true)) {
                throw new \InvalidArgumentException(\sprintf('Cache table "%s" already exists', $name));
            }

            $table = new $tableClass($sizes[0] ?? $sizes[self::KEY_TABLE_SIZE] ?? self::DEFAULT_TABLE_SIZE);
            $table->column(self::COLUMN_EXPIRY, $tableClass::TYPE_INT);
            $table->column(
                self::COLUMN_VALUE,
                $tableClass::TYPE_STRING,
                $sizes[1] ?? $sizes[self::KEY_COLUMN_SIZE] ?? self::DEFAULT_VALUE_COLUMN_SIZE
            );
            $table->create();

            $tables[] = $name;
            $_SERVER[self::getServerTableName($name)] = $table;
        }

        // Create table to count requests
        $countTable = new $tableClass(1);
        $countTable->column(self::REQUEST_COUNT_COLUMN, $tableClass::TYPE_INT);
        $countTable->create();
        $countTable->set(self::REQUEST_COUNT_COLUMN, [self::REQUEST_COUNT_COLUMN => 0]);

        $_SERVER[self::SERVER_REQUEST_COUNT_TABLE_NAME] = $countTable;
        $_SERVER[self::SERVER_TABLE_NAMES] = $tables;
    }

    public static function exists(string $name): bool
    {
        return self::get($name) !== null;
    }

    public static function get(string $name, ?bool $throwOnNull = null): SwooleTable|OpenSwooleTable|null
    {
        $tableClass = self::getTableClass();
        $table = $_SERVER[self::getServerTableName($name)] ?? null;
        $table = $table instanceof $tableClass ? $table : null;

        if (($throwOnNull ?? false) && $table === null) {
            throw new \InvalidArgumentException(\sprintf('Cache table "%s" does not exist', $name));
        }

        return $table;
    }

    public static function onRequest(): void
    {
        // Remove expired rows to prevent memory leaks
        $countTable = $_SERVER[self::SERVER_REQUEST_COUNT_TABLE_NAME] ?? null;
        $tableClass = self::getTableClass();

        if ($countTable instanceof $tableClass) {
            $countTable->incr(self::REQUEST_COUNT_COLUMN, self::REQUEST_COUNT_COLUMN);

            $requestCount = $countTable->get(self::REQUEST_COUNT_COLUMN, self::REQUEST_COUNT_COLUMN);

            if ($requestCount >= self::DEFAULT_REQUEST_COUNT_FLUSH) {
                $now = \time();

                foreach ($_SERVER[self::SERVER_TABLE_NAMES] ?? [] as $name) {
                    $table = $_SERVER[self::getServerTableName($name)] ?? null;

                    if ($table instanceof $tableClass) {
                        foreach ($table as $key => $row) {
                            if ($now >= $row[self::COLUMN_EXPIRY]) {
                                $table->del($key);
                            }
                        }
                    }
                }
            }
        }

        $countTable->set(self::REQUEST_COUNT_COLUMN, [self::REQUEST_COUNT_COLUMN => 0]);
    }

    private static function getTableClass(): string
    {
        return self::$tableClass ??= \class_exists(OpenSwooleTable::class)
            ? OpenSwooleTable::class
            : SwooleTable::class;
    }

    private static function getServerTableName(string $name): string
    {
        return \sprintf(self::SERVER_TABLE_NAME_PATTERN, $name);
    }
}

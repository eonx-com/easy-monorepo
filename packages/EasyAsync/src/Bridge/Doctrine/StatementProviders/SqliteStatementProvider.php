<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Doctrine\StatementProviders;

use EonX\EasyAsync\Bridge\Doctrine\Interfaces\StatementsProviderInterface;

final class SqliteStatementProvider implements StatementsProviderInterface
{
    /**
     * @return iterable<string>
     */
    public static function migrateStatements(): iterable
    {
        yield '
            CREATE TABLE `easy_async_batch_items` (
                `id` CHAR(36) NOT NULL,
                `batch_id` CHAR(36) NOT NULL,
                `target_class` VARCHAR(191) NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `started_at` DATETIME DEFAULT NULL,
                `finished_at` DATETIME DEFAULT NULL,
                `attempts` INT(11) DEFAULT 0 NOT NULL,
                `reason` VARCHAR(191) DEFAULT NULL,
                `reason_params` LONGTEXT DEFAULT NULL,
                `throwable` LONGTEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`)
            );
        ';

        yield '
            CREATE TABLE `easy_async_batches` (
                `id` CHAR(36) NOT NULL,
                `failed` INT(11) DEFAULT 0 NOT NULL,
                `succeeded` INT(11) DEFAULT 0 NOT NULL,
                `processed` INT(11) DEFAULT 0 NOT NULL,
                `total` INT(11) DEFAULT 0 NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `started_at` DATETIME DEFAULT NULL,
                `finished_at` DATETIME DEFAULT NULL,
                `throwable` LONGTEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`)
            );
        ';
    }

    /**
     * @return iterable<string>
     */
    public static function rollbackStatements(): iterable
    {
        yield 'DROP TABLE `easy_async_batch_items`;';
        yield 'DROP TABLE `easy_async_batches`;';
    }
}

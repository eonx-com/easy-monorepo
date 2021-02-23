<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Doctrine\StatementProviders;

use EonX\EasyAsync\Bridge\Doctrine\Interfaces\StatementsProviderInterface;

final class SqlStatementProvider implements StatementsProviderInterface
{
    /**
     * @return iterable<string>
     */
    public static function migrateStatements(): iterable
    {
        yield '
            CREATE TABLE `easy_async_batch_items` (
                `id` CHAR(36) NOT NULL COMMENT "(DC2Type:guid)",
                `batch_id` CHAR(36) NOT NULL COMMENT "(DC2Type:guid)",
                `target_class` VARCHAR(191) NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `started_at` DATETIME DEFAULT NULL,
                `finished_at` DATETIME DEFAULT NULL,
                `attempts` INT(11) DEFAULT 0 NOT NULL,
                `reason` VARCHAR(191) NOT NULL,
                `reason_params` LONGTEXT DEFAULT NULL,
                `throwable` LONGTEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';

        yield '
            CREATE TABLE `easy_async_batches` (
                `id` CHAR(36) NOT NULL COMMENT "(DC2Type:guid)",
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
                PRIMARY KEY(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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

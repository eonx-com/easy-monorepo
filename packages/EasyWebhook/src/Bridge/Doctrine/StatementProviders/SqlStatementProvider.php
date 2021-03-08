<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Doctrine\StatementProviders;

use EonX\EasyWebhook\Bridge\Doctrine\Interfaces\StatementsProviderInterface;

final class SqlStatementProvider implements StatementsProviderInterface
{
    /**
     * @return iterable<string>
     */
    public static function migrateStatements(): iterable
    {
        yield '
            CREATE TABLE `easy_webhooks` (
                `id` CHAR(36) NOT NULL COMMENT "(DC2Type:guid)",
                `method` VARCHAR(10) NOT NULL,
                `url` VARCHAR(191) NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `event` VARCHAR(191) DEFAULT NULL,
                `current_attempt` INT(11) DEFAULT 0 NOT NULL,
                `max_attempt` INT(11) DEFAULT 0 NOT NULL,
                `send_after` DATETIME DEFAULT NULL,
                `class` VARCHAR(191) NOT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';

        yield 'CREATE INDEX send_after_idx ON `easy_webhooks` (`status`, `send_after`)';

        yield '
            CREATE TABLE `easy_webhook_results` (
                `id` CHAR(36) NOT NULL COMMENT "(DC2Type:guid)",
                `method` VARCHAR(10) NOT NULL,
                `url` VARCHAR(191) NOT NULL,
                `http_options` LONGTEXT DEFAULT NULL,
                `response` LONGTEXT DEFAULT NULL,
                `throwable` LONGTEXT DEFAULT NULL,
                `webhook_class` VARCHAR(191) NOT NULL,
                `webhook_id` CHAR(36) NOT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ';
    }

    /**
     * @return iterable<string>
     */
    public static function rollbackStatements(): iterable
    {
        yield 'DROP TABLE `easy_webhook_results`;';
        yield 'DROP TABLE `easy_webhooks`;';
    }
}

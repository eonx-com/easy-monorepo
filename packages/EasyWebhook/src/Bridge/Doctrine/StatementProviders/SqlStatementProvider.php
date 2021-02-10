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
        yield "
            CREATE TABLE `easy_webhooks` (
                `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
                `method` varchar(10) NOT NULL,
                `url` varchar(191) NOT NULL,
                `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                `response` LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)',
                `event` varchar(191) DEFAULT NULL,
                `http_options` LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)',
                `throwable` LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)',
                `current_attempt` int(11) DEFAULT 0 NOT NULL,
                `max_attempt` int(11) DEFAULT 0 NOT NULL,
                `send_after` datetime DEFAULT NULL,
                `class` varchar(191) NOT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    /**
     * @return iterable<string>
     */
    public static function rollbackStatements(): iterable
    {
        yield 'DROP TABLE `easy_webhooks`;';
    }
}

---eonx_docs---
ignore: true
---eonx_docs---

# MySQL Migrations

## Webhooks

```php
<?php

final class WebhooksMigration
{
    public function up(): void
    {
        $this->addSql(
            'CREATE TABLE `easy_webhooks` (
                `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "(DC2Type:guid)",
                `method` varchar(10) NOT NULL,
                `url` varchar(191) NOT NULL,
                `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                `response` LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)",
                `event` varchar(191) DEFAULT NULL,
                `http_options` LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)",
                `throwable` LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)",
                `current_attempt` int(11) DEFAULT 0 NOT NULL,
                `max_attempt` int(11) DEFAULT 0 NOT NULL,
                `class` varchar(191) NOT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );
    }

    public function down(): void
    {
        $this->addSql('DROP TABLE `easy_webhooks`;');
    }
}
```

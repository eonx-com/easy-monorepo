# MySQL Migrations

## Webhooks

```php
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
                `http_options` LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)", 
                `throwable` LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)",
                `current_attempt` int(11) DEFAULT 0 NOT NULL,
                `max_attempt` int(11) DEFAULT 0 NOT NULL,
                `retry_after` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );
        $this->addSql('CREATE INDEX `type` ON `easy_async_jobs` (`type`);');
        $this->addSql('CREATE INDEX `target_type` ON `easy_async_jobs` (`target_type`);');
        $this->addSql('CREATE INDEX `target` ON `easy_async_jobs` (`target_type`, `target_id`);');
    }
    
    public function down(): void 
    {
        $this->addSql('DROP INDEX `target` ON `easy_async_jobs`;');
        $this->addSql('DROP INDEX `target_type` ON `easy_async_jobs`;');
        $this->addSql('DROP INDEX `type` ON `easy_async_jobs`;');
        $this->addSql('DROP TABLE `easy_async_jobs`;');
    }   
}
```

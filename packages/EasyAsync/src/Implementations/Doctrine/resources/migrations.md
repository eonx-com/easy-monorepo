# MySQL Migrations

## Job

```php
final class JobsMigration
{
    public function up(): void 
    {
        $this->addSql(
            'CREATE TABLE `easy_async_jobs` (
                `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "(DC2Type:guid)",
                `finished_at` datetime DEFAULT NULL,
                `started_at` datetime DEFAULT NULL,
                `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                `target_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `target_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `failed` int(11) DEFAULT 0,
                `processed` int(11) DEFAULT 0,
                `succeeded` int(11) DEFAULT 0,
                `total` int(11) DEFAULT 1,
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

## JobLog

```php
final class JobLogsMigration
{
    public function up(): void 
    {
        $this->addSql(
            'CREATE TABLE `easy_async_job_logs` (
                `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "(DC2Type:guid)",
                `finished_at` datetime DEFAULT NULL,
                `started_at` datetime DEFAULT NULL,
                `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                `target_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `target_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `debug_info` longtext COLLATE utf8mb4_unicode_ci COMMENT "(DC2Type:json)",
                `failure_params` longtext COLLATE utf8mb4_unicode_ci COMMENT "(DC2Type:json)",
                `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `job_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "(DC2Type:guid)",
                `validation_errors` longtext COLLATE utf8mb4_unicode_ci COMMENT "(DC2Type:json)",
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );
        $this->addSql('CREATE INDEX `job` ON `easy_async_job_logs` (`job_id`);');       
        $this->addSql('CREATE INDEX `status_per_job` ON `easy_async_job_logs` (`job_id`, `status`);');       
    }
    
    public function down(): void
    {
        $this->addSql('DROP INDEX `status_per_job` ON `easy_async_job_logs`;');
        $this->addSql('DROP INDEX `job` ON `easy_async_job_logs`;');
        $this->addSql('DROP TABLE `easy_async_job_logs`;');
    }   
}
```

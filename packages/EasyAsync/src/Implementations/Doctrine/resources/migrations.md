# MySQL Migrations

## Job

```php
final class JobsMigration
{
    public function up(): void 
    {
        $this->addSql(
            'CREATE TABLE `easy_async_jobs` (
                `id` varchar(36) NOT NULL,
                `finished_at` datetime DEFAULT NULL,
                `started_at` datetime DEFAULT NULL,
                `status` varchar(50) NOT NULL,
                `target_id` varchar(255) NOT NULL,
                `target_type` varchar(255) NOT NULL,
                `type` varchar(255) NOT NULL,
                `failed` int(11) DEFAULT 0,
                `processed` int(11) DEFAULT 0,
                `succeeded` int(11) DEFAULT 0,
                `total` int(11) DEFAULT 1,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
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
                `id` varchar(36) NOT NULL,
                `finished_at` datetime DEFAULT NULL,
                `started_at` datetime DEFAULT NULL,
                `status` varchar(50) NOT NULL,
                `target_id` varchar(255) NOT NULL,
                `target_type` varchar(255) NOT NULL,
                `type` varchar(255) NOT NULL,
                `debug_info` longtext,
                `failure_params` longtext,
                `failure_reason` varchar(255) DEFAULT NULL,
                `job_id` varchar(36) NOT NULL,
                `validation_errors` longtext,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
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

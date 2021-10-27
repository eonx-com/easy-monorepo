---eonx_docs--- ignore: true ---eonx_docs---

# MySQL Migrations

## EasyActivity

```php
final class EasyActivityMigration
{
    public function up(): void
    {
        $this->addSql(
            'CREATE TABLE `easy_activity_logs` (
                `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT "(DC2Type:guid)",
                `actor_type` varchar(255) NOT NULL,
                `actor_id` varchar(255),
                `actor_name` varchar(255),
                `action` varchar(255) NOT NULL,
                `subject_type` varchar(255) NOT NULL,
                `subject_id` varchar(255) NOT NULL,
                `subject_data` text,
                `subject_old_data` text,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );
    }

    public function down(): void
    {
        $this->addSql('DROP TABLE `easy_activity_logs`;');
    }
}
```

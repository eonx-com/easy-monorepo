<!---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs--->

# Configuration

By default, the EasyActivity package includes all properties of subjects in activity log entries.
If desired, you can configure activity log entries for each subject to only include specific properties
or to always exclude specific properties.

To see the available configuration options, run the following command:

```bash
php bin/console config:dump-reference EasyActivityBundle
```

## Example configuration file

An example configuration file `config/packages/easy_activity.php`:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Entity\SomeEntity;
use App\Entity\SomeOtherEntity;
use Symfony\Config\EasyActivityConfig;

return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig
        ->tableName('activity_logs')
        ->disallowedProperties([
            'updatedAt',
        ]);

    $easyActivityConfig->subjects(SomeEntity::class)
        ->allowedProperties([
            'content',
            'description',
        ])
        ->disallowedProperties([
            'author',
        ])
        ->nestedObjectAllowedProperties([
            SomeOtherEntity::class => [
                'processingDate',
            ]
        ])
        ->type('SomeEntity');
};

```

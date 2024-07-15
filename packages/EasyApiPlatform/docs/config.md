---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

# Configuration

To see the available configuration options, run the following command:

```bash
php bin/console config:dump-reference EasyApiPlatformBundle
```

## Example configuration file

An example configuration file `config/packages/easy_api_platform.php`:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyApiPlatformConfig;

return static function (EasyApiPlatformConfig $easyApiPlatformConfig): void {
    $easyApiPlatformConfig->advancedSearchFilter()
        ->iriFields(['entityId']);
};
```

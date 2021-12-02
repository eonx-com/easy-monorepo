---eonx_docs---
title: CacheConfigFinder In Symfony
weight: 1001
---eonx_docs---

### Override Config Cache in YAML

Most Symfony projects are still using YAML to define their services. You can override the config cache strategy in YAML:

```yaml
# config/services.yaml

easy_notification.config_cache:
    class: Symfony\Component\Cache\Adapter\PhpFilesAdapter
    arguments:
        $namespace: 'eonx_notification_config'
```

<br>

### Override Config Cache in PHP

If your Symfony project is using PHP to define its services, the service id to override is provided to you via
a constant on `EonX\EasyNotification\Bridge\BridgeConstantsInterface`.

```php
// config/services.php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(BridgeConstantsInterface::SERVICE_CONFIG_CACHE, PhpFilesAdapter::class)
        ->arg('$namespace', 'eonx_notification_config');
};
```

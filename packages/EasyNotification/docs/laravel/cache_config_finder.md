---eonx_docs---
title: CacheConfigFinder In Laravel
weight: 2001
---eonx_docs---

Once all dependencies installed, you can override the default config caching within the service provider of your choice.
The service id to override is provided to you via `\EonX\EasyNotification\Bundle\Enum\ConfigServiceId`.

```php
// app/Providers/NotificationServiceProvider.php

use EonX\EasyNotification\Bundle\Enum\ConfigServiceId;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConfigServiceId::ConfigCache->value, static function(): CacheInterface {
            return new PhpFilesAdapter('eonx_notification_config');
        });
    }
}
```

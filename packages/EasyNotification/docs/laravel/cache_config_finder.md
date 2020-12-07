---eonx_docs---
title: CacheConfigFinder In Laravel
weight: 2001
---eonx_docs---

Once all dependencies installed, you can override the default config caching within the service provider of your choice.
The service id to override is provided to you via a constant on `EonX\EasyNotification\Bridge\BridgeConstantsInterface`.

```php
// app/Providers/NotificationServiceProvider.php

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BridgeConstantsInterface::SERVICE_CONFIG_CACHE, static function(): CacheInterface {
            return new PhpFilesAdapter('eonx_notification_config');
        });
    }
}
```

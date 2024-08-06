---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundle

If you're using [Symfony Flex][1], this step has been done automatically for you. If not, you can register the bundle
yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyNotification\Bundle\EasyNotificationBundle::class => ['all' => true],
];
```

<br>

### Configuration

The only required configuration is the API URL of the EonX Notification service your application is working with.

```php
# config/packages/easy_notification.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyNotificationConfig;

return static function (EasyNotificationConfig $easyNotificationConfig): void {
    $easyNotificationConfig
        ->apiUrl(env('EONX_NOTIFICATION_API_URL'))
        ->configExpiresAfter(500); // Number of seconds
};

```

[1]: https://symfony.com/doc/current/setup/flex.html

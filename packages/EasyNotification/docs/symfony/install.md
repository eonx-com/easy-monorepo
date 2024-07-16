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

```yaml
# config/packages/easy_notification.yaml

easy_notification:
    api_url: '%env(EONX_NOTIFICATION_API_URL)%'

    # You can optionally customise the expiry time for the cached config here.
    config_expires_after: 500 # Number of seconds
```

[1]: https://symfony.com/components/Symfony%20Flex

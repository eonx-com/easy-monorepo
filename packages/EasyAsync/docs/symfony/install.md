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

    EonX\EasyAsync\Bundle\EasyAsyncBundle::class => ['all' => true],
];
```

### Configure Messenger Middleware

By default, all EasyAsync Messenger middleware are enabled.

You can disable all middleware at once:

```php
use Symfony\Config\EasyAsyncConfig;

return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $easyAsyncConfig
        ->messenger()
        ->middleware()
        ->enabled(false);
};
```

Or disable individual middleware while keeping the others enabled:

```php
use Symfony\Config\EasyAsyncConfig;

return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $middlewareConfig = $easyAsyncConfig
        ->messenger()
        ->middleware();

    $middlewareConfig->doctrineManagersSanityCheck()
        ->enabled(false);

    $middlewareConfig->doctrineManagersClear()
        ->enabled(true);
};
```

[1]: https://symfony.com/doc/current/setup/flex.html

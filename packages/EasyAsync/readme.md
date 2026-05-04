---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-async
```

With Symfony, EasyAsync Messenger middleware are enabled by default and can be toggled globally or per middleware:

```php
use Symfony\Config\EasyAsyncConfig;

return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $middlewareConfig = $easyAsyncConfig
        ->messenger()
        ->middleware();

    $middlewareConfig->enabled(true);
    $middlewareConfig->doctrineManagersSanityCheck()->enabled(false);
    $middlewareConfig->doctrineManagersClear()->enabled(true);
};
```

[1]: https://getcomposer.org/

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
    
    EonX\EasyLock\Bridge\Symfony\EasyLockBundle::class => ['all' => true],
];
```

### Messenger Integration

When running multiple workers simultaneously, it is a good practice to implement a locking mechanism to guarantee
a single queue message is handled only once. A common use case, multiple workers consume the same message at the same 
time.

This package comes with a Messenger Middleware handling lock out of the box. Two options are available:

- Make your message implement `EonX\EasyLock\Interfaces\WithLockDataInterface`
- Add `EonX\EasyLock\Bridge\Symfony\Messenger\WithLockDataStamp` to the envelope

::: tip | Tip
Remember to update your messenger configuration to add the middleware
:::

[1]: https://flex.symfony.com/

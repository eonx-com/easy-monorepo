---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundle

If you're using [Symfony Flex](https://symfony.com/doc/current/setup/flex.html), this step has been done automatically for you.
If not, you can register the bundle yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyRandom\Bundle\EasyRandomBundle::class => ['all' => true],
];
```

### Configuration

To configure UUID version use the [Symfony configuration](https://symfony.com/blog/new-in-symfony-5-3-uid-improvements)

[1]:

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

    EonX\EasyHttpClient\Bridge\Symfony\EasyHttpClientSymfonyBundle::class => ['all' => true],
];
```

[1]: https://flex.symfony.com/

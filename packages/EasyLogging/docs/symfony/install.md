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

    EonX\EasyLogging\Bundle\EasyLoggingBundle::class => ['all' => true],
];
```

<br>

### Configuration

There is no required configuration, but if you want to specify a custom default channel you can do it.

```yaml
# config/packages/easy_logging.yaml

easy_logging:
    default_channel: 'my-channel'
```

[1]: https://symfony.com/components/Symfony%20Flex

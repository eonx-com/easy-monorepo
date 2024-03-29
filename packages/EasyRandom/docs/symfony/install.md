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

    EonX\EasyRandom\Bridge\Symfony\EasyRandomSymfonyBundle::class => ['all' => true],
];
```

### Configuration

If the [UID Component](https://symfony.com/doc/current/components/uid.html) installed than `\EonX\EasyRandom\Bridge\Symfony\Uid\UuidGenerator` uses for generating UUIDs.
To configure UUID version use the [Symfony configuration](https://symfony.com/blog/new-in-symfony-5-3-uid-improvements)

Of course, you can also create your own generator by implementing the `EonX\EasyRandom\Interfaces\UuidGeneratorInterface` interface
and register it in your container.

[1]: https://flex.symfony.com/

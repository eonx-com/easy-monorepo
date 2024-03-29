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

<br>

### Configuration

```yaml
# config/packages/easy_random.yaml

easy_random:
    uuid_version: 6 # Default value

```

You can configure the UUID version to use for the `EonX\EasyRandom\Interfaces\UuidGeneratorInterface` service.
The default value is `6`. The possible values are `4` and `6`.

The following classes will be used depending on the version you choose:

- Version 4: `EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV4Generator` (the `EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV4Generator` class if the "symfony/uid" package is not installed)
- Version 6: `EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator` (the `EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV6Generator` class if the "symfony/uid" package is not installed)

Of course, you can also create your own generator by implementing the `EonX\EasyRandom\Interfaces\UuidGeneratorInterface` interface
and register it in your container.

<br>

[1]: https://flex.symfony.com/

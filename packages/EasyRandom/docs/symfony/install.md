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

### UUID V4 Generator

In the case you want to generate UUID V4 in your application, you will need to set the UUID V4 generator of your choice
onto the random generator instance. To do so, you will first need to register the UUID V4 generator of your choice as
a service and, then set the service id into the bundle configuration.

#### Register UUID V4 generator as a service

By default, this package will register the built-in supported implementations as services:

- *easy_random.ramsey_uuid4* alias for `EonX\EasyRandom\Generators\RamseyUuidV4Generator`
- *easy_random.symfony_uuid4* alias for `EonX\EasyRandom\Generators\SymfonyUidUuidV4Generator`

#### Set the service id into the bundle configuration

```yaml
# config/packages/easy_random.yaml

easy_random:
    uuid_v4_generator: EonX\EasyRandom\Generators\RamseyUuidV4Generator # The service id
```

[1]: https://flex.symfony.com/

---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundle

If you're using [Symfony Flex][1], this step has been done automatically for you. If not, you can register the bundle yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyActivity\Bridge\Symfony\EasyActivitySymfonyBundle::class => ['all' => true],
];
```

### Configure another bundle
We are suggesting to install the following packages:
* [eonx-com/easy-doctrine](2): to create entries from doctrine events.
* [symfony/serializer](3): to serialize the Subject's data.
* [symfony/messenger](4): to store entries asynchronously.


[1]: https://flex.symfony.com/
[2]: https://github.com/eonx-com/easy-doctrine
[3]: https://github.com/symfony/serializer
[4]: https://github.com/symfony/messenger
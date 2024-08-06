---eonx_docs---
title: Installation
weight: 1000
---eonx_docs---

# Installation

## Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-activity
```

## Symfony

### Register bundle

If you're using [Symfony Flex][2], this step has been done automatically for you. If not, you can register the bundle yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyActivity\Bundle\EasyActivityBundle::class => ['all' => true],
];
```

### Install additional packages

We suggest that you install additional packages to maximize the utility of the EasyActivity package. Install the
following packages for the following purposes:

- [eonx-com/easy-doctrine][3]: to create entries from Doctrine events
- [symfony/serializer][4]: to serialize the subject's data
- [symfony/messenger][5]: to store entries asynchronously

[1]: https://getcomposer.org/

[2]: https://symfony.com/doc/current/setup/flex.html

[3]: https://packages.eonx.com/packages/easy-doctrine/

[4]: https://symfony.com/doc/current/components/serializer.html

[5]: https://symfony.com/doc/current/components/messenger.html

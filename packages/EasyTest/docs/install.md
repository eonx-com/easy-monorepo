---eonx_docs---
title: Installation
weight: 1000
---eonx_docs---

# Installation

## Require package (Composer)

The recommended way to install this package is to use [Composer](https://getcomposer.org/):

```bash
$ composer require eonx-com/easy-test
```

## Symfony

### Register bundle

If you're using [Symfony Flex](https://flex.symfony.com/), this step has been done automatically for you. If not, you can register the bundle yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyTest\Bridge\Symfony\EasyTestSymfonyBundle::class => ['test' => true],
];
```

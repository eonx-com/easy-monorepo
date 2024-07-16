---eonx_docs---
title: Installation
weight: 1000
---eonx_docs---

# Installation

## Using Composer

To install this package, we recommend using [Composer][https://getcomposer.org/]:

```bash
$ composer require eonx-com/easy-api-platform
```

## Integrating with Symfony

### Registering the Bundle

If you're using [Symfony Flex][https://symfony.com/components/Symfony%20Flex], the bundle registration step has already been done for you automatically. If not, follow these simple steps to register the bundle manually:

1. Open the `config/bundles.php` file in your Symfony project.

2. Add the following line to the file:

   ```php
   // config/bundles.php

   return [
       // Other bundles ...

       \EonX\EasyApiPlatform\Bundle\EasyApiPlatformBundle::class => ['all' => true],
   ];
   ```

That's it! You have now successfully installed and registered the EasyApiPlatform bundle for use in your Symfony application. Happy coding!

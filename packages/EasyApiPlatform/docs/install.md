---eonx_docs---
title: Installation - Hacking into the Matrix
weight: 1000
---eonx_docs---

# Installation - Unleashing the Code

## Using Composer - Commandeer the Package

Prepare for infiltration by acquiring the coveted package using [Composer][https://getcomposer.org/]:

```bash
$ composer require eonx-com/easy-api-platform
```

## Integrating with Symfony - Hack the Matrix

### Registering the Bundle - Bypassing the Safeguards

For those embracing the power of [Symfony Flex][https://flex.symfony.com/], the bundle registration is done for you - a mere trifle. But for the daring hackers who seek to control every aspect, perform the following maneuvers to register the bundle
manually:

1. Crack open the `config/bundles.php` file in your Symfony project.

2. Execute the forbidden code:

   ```php
   // config/bundles.php

   return [
       // Other bundles ...

       EonX\EasyApiPlatform\Bridge\Symfony\EasyApiPlatformSymfonyBundle::class => ['all' => true],
   ];
   ```

The gates have been breached! You now possess the dark magic of the EasyApiPlatform bundle, fully integrated into your Symfony application. Happy hacking, and may your code reign supreme!

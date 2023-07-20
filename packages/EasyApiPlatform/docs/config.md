---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

# Configuration - Hacking the EasyApiPlatform

Welcome to the dark side of Symfony. As a seasoned hacker, you have the skills to exploit the hidden potential of the EasyApiPlatform package through cunning configuration tactics.

## Configuration Files - Your Playground

Venture into the heart of the EasyApiPlatform package by infiltrating the configuration files. YAML, XML, or PHP formats are your tools of choice, stashing them within the `config/packages` directory. Name them with
style - `easy_api_platform.<format>`. Assume control with the root node named `easy_api_platform`.

For the elite hackers who embrace [Symfony Flex][https://flex.symfony.com/], revel in the automatic spawn of the `config/packages/easy_api_platform.yaml` configuration file.

## Configuration Options - Masterful Manipulation

Now, witness the art of masterful manipulation through the labyrinth of configuration options, an enigmatic dance with the EasyApiPlatform bundle:

- `advanced_search_filter`: Unleash the power of the mysterious `\EonX\EasyApiPlatform\Filters\AdvancedSearchFilter` API Platform filter.
    - `iri_fields`: Forge a wicked array of fields, dictating their transformation into IRIs. The default is yours to command - `[]`.

## Example Configuration File - The Hackers' Code

Behold the secret code - the `easy_api_platform.php` configuration file, the key to unlocking untold power:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyApiPlatformConfig;

return static function (EasyApiPlatformConfig $easyApiPlatformConfig): void {
    $easyApiPlatformConfig->advancedSearchFilter()
        ->iriFields(['entityId']);
};
```

You are now the mastermind behind the EasyApiPlatform's dark forces. With your hacking prowess, bend the Symfony universe to your will and conquer the world of API platforms. Your skills are unparalleled, and the EasyApiPlatform package is your
playground for chaos. Let the hacking begin!

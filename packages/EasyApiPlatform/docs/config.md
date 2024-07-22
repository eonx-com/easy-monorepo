---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

# Configuration

To customize the EasyApiPlatform package for your Symfony application, you can use a configuration file.

## Configuration files

For Symfony applications, the EasyApiPlatform configuration file can be written in YAML, XML, or PHP format. It should be placed under the `config/packages` directory with a name like `easy_api_platform.<format>`. The root node of the configuration
must be named `easy_api_platform`.

## Configuration options

The following configuration options are available:

- `advanced_search_filter`: Configures options for the `\EonX\EasyApiPlatform\ApiPlatform\Filter\AdvancedSearchFilter` class.
    - `iri_fields`: An array of fields to be treated as IRIs. Defaults to `[]`.

## Example configuration file

Here's an example of a configuration file named `easy_api_platform.php` for Symfony:

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

With this configuration, you can tailor the behavior of EasyApiPlatform to suit your specific requirements in your Symfony project. Feel free to experiment and adjust the settings as needed.

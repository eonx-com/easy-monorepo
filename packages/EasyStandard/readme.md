---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

This package is a way to centralise reusable classes used for coding standards. It contains:

- [Rectors][2]
- [Sniffs][3]

<br>

### Require package (Composer)

We recommend to use [Composer][1] to manage your dependencies. You can require this package as follows:

```bash
$ composer require --dev eonx/easy-standard
```

### Prepare configuration file

You can use one of the following names for configuration: `easy-coding-standard.yml`, `easy-coding-standard.yaml`, `ecs.yml`, `ecs.yaml` or `ecs.php`. Create this file in the root folder of the project.

The basic structure of configuration:
```php
// ecs.php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    /*
     * List of parameters
     */

    $services = $containerConfigurator->services();
    /*
     * List of services
     */
};

```

### Run check

Go to the root of project and run `vendor/bin/ecs check`.

Expected output: `[OK] No errors found. Great job - your code is shiny in style!`

[1]: https://getcomposer.org/
[2]: https://github.com/rectorphp/rector
[3]: https://github.com/squizlabs/PHP_CodeSniffer

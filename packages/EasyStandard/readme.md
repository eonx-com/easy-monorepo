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

### Prepare configuration file for ecs

You can use one of the following names for a configuration: `easy-coding-standard.yml`, `easy-coding-standard.yaml`, `ecs.yml`, `ecs.yaml` or `ecs.php`. Create this file in the root folder of the project.

The basic structure of configuration:
```yaml
# ecs.yml
parameters:
    /*
     * List of parameters
     */
services:
    /*
     * List of services
     */
```
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

### Run ecs check

Go to the root of project and run `vendor/bin/ecs check`.

Expected output: `[OK] No errors found. Great job - your code is shiny in style!`

### Prepare configuration file for rector

You can use one of the following names for a configuration: `rector.yml` or `rector.yaml`. Create this file in the root folder of the project.

The basic structure of configuration:
```yaml
# rector.yml
parameters:
    # list of parameters

services:
    # list of services with their configurations
```
```php
// rector.php
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
### Run rector check

Go to the root of project and run
```bash
touch `php -r "echo sys_get_temp_dir() . '/_rector_type_probe.txt';"` && vendor/bin/rector process --dry-run
```

Expected output: `[OK] Rector is done!`

[1]: https://getcomposer.org/
[2]: https://github.com/rectorphp/rector
[3]: https://github.com/squizlabs/PHP_CodeSniffer

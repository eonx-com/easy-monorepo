---eonx_docs---
title: Services
weight: 2000
is_section: true
---eonx_docs---
```yml
// ecs.yml
services:
    PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff:
        absoluteLineLimit: 120
        ignoreComments: false
```
```php
// ecs.php
declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    
    $services->set(LineLengthSniff::class)
        ->property('absoluteLineLimit', 120)
        ->property('ignoreComments', false);
};

```

### List of services

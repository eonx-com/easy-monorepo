---eonx_docs---
title: Settings of services
weight: 2000
is_section: true
---eonx_docs---
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

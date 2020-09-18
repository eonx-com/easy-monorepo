---eonx_docs---
title: Parameters
weight: 1000
is_section: true
---eonx_docs---
```yml
// ecs.yml
parameters:
    sets:
        - 'psr12'
```
```php
// ecs.php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\Configuration\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    
    $parameters->set(Option::SETS, [
        SetList::PSR_12,
    ]);
};

```

### List of parameters

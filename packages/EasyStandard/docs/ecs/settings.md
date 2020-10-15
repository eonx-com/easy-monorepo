---eonx_docs---
title: Settings of ecs
weight: 1000
is_section: true
---eonx_docs---

### Example of configuration
```yaml
# ecs.yaml
parameters:
    sets:
        - 'psr12'
    paths:
        - 'app'
        - 'tests'
    skip:
        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff: ~
        SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff:
            - 'path/to/file.php'
            - 'path/to/folder/*'
    exclude_paths:
        - 'path/to/file.php'
        - 'path/with/mask/**/*.php'

services:
    PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff:
        absoluteLineLimit: 120
        ignoreComments: false
```
```php
// ecs.php
declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\Configuration\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ]);
    
    $parameters->set(Option::SETS, [
        SetList::PSR_12,
    ]);
    
    $parameters->set(Option::SKIP, [
        LineLengthSniff::class => null,
        StaticClosureSniff::class => [
            __DIR__ . '/path/to/file.php',
            __DIR__ . '/path/to/folder/*',
        ],
    ]);
    
    $parameters->set(Option::EXCLUDE_PATHS, [
        __DIR__ . '/path/to/file.php',
        __DIR__ . '/path/with/mask/**/*.php',
    ]);
    
    $services = $containerConfigurator->services();
    
    $services->set(LineLengthSniff::class)
        ->property('absoluteLineLimit', 120)
        ->property('ignoreComments', false);

};
```

### List of parameters

- `exclude_paths` - skip directory and/or file
- `paths` - paths to analyze
- `sets` - list of rules using during analyze
- `skip` - skip directory and/or file by rule

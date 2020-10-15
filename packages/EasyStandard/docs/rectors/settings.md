---eonx_docs---
title: Settings of Rector
weight: 2000
is_section: true
---eonx_docs---

### Example of configuration

```yaml
parameters:
    auto_import_names: true
    import_short_classes: true
    import_doc_blocks: false
    php_version_features: '7.4'
    autoload_paths:
        - '.phpunit/phpunit-8.5-0/src'
    paths:
        - 'src'
        - 'tests'
    exclude_paths:
        - 'path/to/folder/*'
    skip:
        Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector: ~
        Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector:
            - 'path/to/file.php'
            - 'path/to/folder/*'

services:
    EonX\EasyStandard\Rector\StrictInArrayRector: ~

    Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector: ~
    Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector: ~
    
    EonX\EasyStandard\Sniffs\Commenting\AnnotationSortingSniff:
        alwaysTopAnnotations:
            - '@param'
            - '@return'
            - '@throws'
```
```php
// rector.php
declare(strict_types=1);

use EonX\EasyStandard\Rector\StrictInArrayRector;
use EonX\EasyStandard\Sniffs\Commenting\AnnotationSortingSniff;
use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector;
use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    $parameters->set(Option::PHP_VERSION_FEATURES, '7.4');
    
    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/phpunit/phpunit-8.5-0/src',
    ]);
    
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    $parameters->set(Option::EXCLUDE_PATHS, [
            __DIR__ . '/path/to/folder/*',
    ]);
    
    $parameters->set(Option::SKIP, [
        CallableThisArrayToAnonymousFunctionRector::class => null,
        ArrayThisCallToThisMethodCallRector::class => [
            __DIR__ . '/path/to/file.php',
            __DIR__ . '/path/to/folder/*',
        ],
    ]);

    $services = $containerConfigurator->services();
    
    $services->set(StrictInArrayRector::class);
    $services->set(ThrowWithPreviousExceptionRector::class);
    $services->set(ExplicitBoolCompareRector::class);
    $services->set(AnnotationSortingSniff::class)
        ->property('alwaysTopAnnotations', [
            '@param',
            '@return',
            '@throws',
        ]);
};
```

### List of parameters

- `auto_import_names` - auto import fully qualified class names? [default: false]
- `autoload_paths` - rector relies on autoload setup of your project; Composer autoload is included by default
- `exclude_paths` - skip directory and/or file
- `exclude_rectors` - is there single rule you don't like from a set you use?
- `import_doc_blocks` - skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
- `import_short_classes` - skip root namespace classes, like \DateTime or \Exception [default: true]
- `paths` - paths to analyze
- `php_version_features` - is your PHP version different from the one your refactor to? [default: your PHP version]
- `skip` - skip directory and/or file by rule

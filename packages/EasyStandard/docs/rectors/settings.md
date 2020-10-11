---eonx_docs---
title: Settings of rector
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
            - '/path/to/file.php'
            - '/path/to/folder/*'

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

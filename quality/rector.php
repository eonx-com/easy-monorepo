<?php
declare(strict_types=1);

use EonX\EasyQuality\Helper\ParallelSettingsHelper;
use EonX\EasyQuality\Rector\AddSeeAnnotationRector;
use EonX\EasyQuality\Rector\SingleLineCommentRector;
use EonX\EasyQuality\ValueObject\EasyQualitySetList;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../bin',
        __DIR__ . '/../config',
        __DIR__ . '/../monorepo',
        __DIR__ . '/../monorepo-builder.php',
        __DIR__ . '/../packages',
        __DIR__ . '/../tests',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ])
    ->withParallel(
        ParallelSettingsHelper::getTimeoutSeconds(),
        ParallelSettingsHelper::getMaxNumberOfProcess(),
        ParallelSettingsHelper::getJobSize()
    )
    ->withImportNames(importDocBlockNames: false)
    ->withPhpSets(php84: true)
    ->withCache(__DIR__ . '/var/cache/rector', FileCacheStorage::class)
    ->withBootstrapFiles([
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/stubs/pcntl.php.stub',
    ])
    ->withSets([
        EasyQualitySetList::RECTOR,
        EasyQualitySetList::RECTOR_PHPUNIT_10,
    ])
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class => null,
        ArrayToFirstClassCallableRector::class => [
            'packages/EasyBatch/tests/Stub/Kernel/KernelStub.php',
            'packages/EasyLock/bundle/CompilerPass/RegisterLockStoreServiceCompilerPass.php',
            'packages/EasyPagination/tests/Stub/Kernel/KernelStub.php',
        ],
        ClassPropertyAssignToConstructorPromotionRector::class => [
            'packages/*/ApiResource/*',
            'packages/*/Entity/*',
        ],
        ClosureToArrowFunctionRector::class => [
            'packages/EasyApiPlatform/src/EasyErrorHandler/Builder/AbstractApiPlatformSerializerExceptionErrorResponseBuilder.php',
        ],
        ReadOnlyPropertyRector::class => [
            'packages/EasyDoctrine/src/EntityEvent/EntityManager/WithEventsEntityManager.php',
        ],
        'packages/*/config/reference.php',
        'packages/*/var/*',
        'packages/*/vendor/*',
    ])
    ->withRules([
        AddSeeAnnotationRector::class,
    ])
    ->withConfiguredRule(SingleLineCommentRector::class, [[]]);

<?php
declare(strict_types=1);

use EonX\EasyQuality\Helper\ParallelSettingsHelper;
use EonX\EasyQuality\Rector\AddSeeAnnotationRector;
use EonX\EasyQuality\Rector\SingleLineCommentRector;
use EonX\EasyQuality\ValueObject\EasyQualitySetList;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

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
    ->withPhpSets(php82: true)
    ->withCache(__DIR__ . '/var/cache/rector', FileCacheStorage::class)
    ->withBootstrapFiles([
        __DIR__ . '/../vendor/autoload.php',
    ])
    ->withSets([
        EasyQualitySetList::RECTOR,
        EasyQualitySetList::RECTOR_PHPUNIT_10,
    ])
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class => [
            'packages/*/ApiResource/*',
            'packages/*/Entity/*',
        ],
        FirstClassCallableRector::class => [
            'packages/EasyBatch/tests/Stub/Kernel/KernelStub.php',
            'packages/EasyBugsnag/tests/Stub/Kernel/KernelStub.php',
            'packages/EasyDoctrine/bundle/config/services.php',
            'packages/EasyLock/bundle/CompilerPass/RegisterLockStoreServiceCompilerPass.php',
            'packages/EasyLock/tests/Fixture/config/in_memory_connection.php',
            'packages/EasyPagination/tests/Stub/Kernel/KernelStub.php',
        ],
        'packages/*/var/*',
        'packages/*/vendor/*',
    ])
    ->withRules([
        AddSeeAnnotationRector::class,
    ])
    ->withConfiguredRule(SingleLineCommentRector::class, [[]]);

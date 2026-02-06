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
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;

// @todo Consider PHPUnitSetList::PHPUNIT_120 and PHPUnitSetList::PHPUNIT_CODE_QUALITY,
$temporaryIgnoredRectors = [
    AddArrowFunctionReturnTypeRector::class => null,
    AddTypeToConstRector::class => null,
    ClosureToArrowFunctionRector::class => null,
    ForeachToArrayAllRector::class => null,
    ForeachToArrayAnyRector::class => null,
    NewMethodCallWithoutParenthesesRector::class => null,
    NullToStrictStringFuncCallArgRector::class => null,
];

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
        DeprecatedAnnotationToDeprecatedAttributeRector::class => null,
        ReadOnlyPropertyRector::class => [
            'packages/EasyDoctrine/src/EntityEvent/EntityManager/WithEventsEntityManager.php',
        ],
        'packages/*/config/reference.php',
        'packages/*/var/*',
        'packages/*/vendor/*',
        ...$temporaryIgnoredRectors,
    ])
    ->withRules([
        AddSeeAnnotationRector::class,
    ])
    ->withConfiguredRule(SingleLineCommentRector::class, [[]]);

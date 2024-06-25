<?php
declare(strict_types=1);

use EonX\EasyQuality\Rector\AddSeeAnnotationRector;
use EonX\EasyQuality\Rector\SingleLineCommentRector;
use EonX\EasyQuality\ValueObject\EasyQualitySetList;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../bin',
        __DIR__ . '/../config',
        __DIR__ . '/../monorepo',
        __DIR__ . '/../monorepo-builder.php',
        __DIR__ . '/../packages',
        __DIR__ . '/../tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ])
    ->withParallel(timeoutSeconds: 300, maxNumberOfProcess: 2, jobSize: 20)
    ->withImportNames(importDocBlockNames: false)
    ->withPhpSets(php81: true)
    ->withCache(__DIR__ . '/var/cache/rector', FileCacheStorage::class)
    ->withBootstrapFiles([
        __DIR__ . '/../vendor/autoload.php',
    ])
    ->withSets([
        EasyQualitySetList::RECTOR,
        PHPUnitSetList::PHPUNIT_100,
    ])
    ->withSkip([
        // Skip entire files or directories
        'packages/*/var/*', // Cache files
        'packages/*/vendor/*', // Composer dependencies installed locally for development and testing
        'packages/EasyEncryption/src/AwsPkcs11Encryptor.php', // Because of Pkcs11

        // Skip rules
        AddLiteralSeparatorToNumberRector::class => [
            'packages/EasyApiToken/tests/AbstractFirebaseJwtTokenTestCase.php',
            'packages/EasyUtils/tests/Bridge/Symfony/Validator/Constraints/AbnValidatorTest.php',
        ],
        ClassPropertyAssignToConstructorPromotionRector::class,
        FinalizeClassesWithoutChildrenRector::class => [
            'packages/EasySecurity/src/SecurityContext.php',
            'packages/EasyTest/src/InvalidDataMaker/InvalidDataMaker.php',
        ],
        FinalizePublicClassConstantRector::class,
        FirstClassCallableRector::class => [
            'packages/EasyActivity/tests/Bridge/Symfony/Stubs/KernelStub.php',
            'packages/EasyBatch/tests/Stub/HttpKernel/KernelStub.php',
            'packages/EasyBugsnag/tests/Bridge/Symfony/Stubs/KernelStub.php',
            'packages/EasyDoctrine/bundle/config/services.php',
            'packages/EasyLock/bundle/CompilerPass/RegisterLockStoreServiceCompilerPass.php',
            'packages/EasyLock/tests/Fixture/config/in_memory_connection.php',
            'packages/EasyPagination/tests/Bridge/Symfony/Stubs/KernelStub.php',
        ],
        JsonThrowOnErrorRector::class,
        ReadOnlyPropertyRector::class,
    ])
    ->withRules([
        AddSeeAnnotationRector::class,
    ])
    ->withConfiguredRule(SingleLineCommentRector::class, [[]]);

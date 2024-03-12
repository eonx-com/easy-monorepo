<?php
declare(strict_types=1);

use EonX\EasyQuality\Rector\AddSeeAnnotationRector;
use EonX\EasyQuality\Rector\PhpDocCommentRector;
use EonX\EasyQuality\Rector\ReturnArrayToYieldRector;
use EonX\EasyQuality\Rector\SingleLineCommentRector;
use EonX\EasyQuality\Rector\UselessSingleAnnotationRector;
use EonX\EasyQuality\Rector\ValueObject\ReturnArrayToYield;
use EonX\EasyQuality\ValueObject\EasyQualitySetList;
use PHPUnit\Framework\TestCase;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory(__DIR__ . '/var/cache/rector');

    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    $rectorConfig->sets([
        EasyQualitySetList::RECTOR,
        LevelSetList::UP_TO_PHP_81,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->autoloadPaths([__DIR__ . '/../vendor']);

    $rectorConfig->bootstrapFiles([
        __DIR__ . '/../vendor/autoload.php',
    ]);

    $rectorConfig->importNames(importDocBlockNames: false);

    $rectorConfig->importShortClasses();

    $rectorConfig->parallel(maxNumberOfProcess: 2, jobSize: 1);

    $rectorConfig->paths([
        __DIR__ . '/../bin',
        __DIR__ . '/../config',
        __DIR__ . '/../monorepo',
        __DIR__ . '/../monorepo-builder.php',
        __DIR__ . '/../packages',
        __DIR__ . '/../tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);

    $rectorConfig->skip([
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
        CountOnNullRector::class,
        FinalizeClassesWithoutChildrenRector::class => [
            'packages/EasyDoctrine/src/Listeners/EntityEventListener.php',
            'packages/EasyDoctrine/src/Listeners/TimestampableEventListener.php',
            'packages/EasySecurity/src/SecurityContext.php',
            'packages/EasyTest/src/InvalidDataMaker/InvalidDataMaker.php',
        ],
        FinalizePublicClassConstantRector::class,
        FirstClassCallableRector::class => [
            'packages/EasyActivity/tests/Bridge/Symfony/Stubs/KernelStub.php',
            'packages/EasyBatch/tests/Bridge/Symfony/Stubs/KernelStub.php',
            'packages/EasyBugsnag/tests/Bridge/Symfony/Stubs/KernelStub.php',
            'packages/EasyDoctrine/src/Bridge/Symfony/Resources/config/services.php',
            'packages/EasyLock/src/Bridge/Symfony/DependencyInjection/Compiler/RegisterLockStoreServicePass.php',
            'packages/EasyPagination/tests/Bridge/Symfony/Stubs/KernelStub.php',
        ],
        JsonThrowOnErrorRector::class,
        PhpDocCommentRector::class => [
            'packages/EasyApiPlatform/src/Filters/AdvancedSearchFilter.php',
        ],
        ReadOnlyPropertyRector::class,
    ]);

    $rectorConfig->rule(AddSeeAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(PhpDocCommentRector::class, [[]]);
    $rectorConfig->ruleWithConfiguration(SingleLineCommentRector::class, [[]]);

    $rectorConfig->ruleWithConfiguration(ReturnArrayToYieldRector::class, [
        ReturnArrayToYieldRector::METHODS_TO_YIELDS => [
            new ReturnArrayToYield(TestCase::class, 'provide*'),
        ],
    ]);

    $rectorConfig->ruleWithConfiguration(UselessSingleAnnotationRector::class, [
        UselessSingleAnnotationRector::ANNOTATIONS => ['{@inheritDoc}'],
    ]);
};

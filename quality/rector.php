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
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(EasyQualitySetList::RECTOR);
    $rectorConfig->import(LevelSetList::UP_TO_PHP_81);

    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    $rectorConfig->autoloadPaths([__DIR__ . '/../vendor']);

    $rectorConfig->bootstrapFiles([
        __DIR__ . '/../vendor/autoload.php',
    ]);

    $rectorConfig->importNames(importDocBlockNames: false);

    $rectorConfig->importShortClasses();

    $rectorConfig->parallel(maxNumberOfProcess: 2, jobSize: 1);

    $rectorConfig->paths([
        __DIR__ . '/../packages',
        __DIR__ . '/../tests',
    ]);

    $rectorConfig->skip([
        // Skip entire files
        'packages/EasyEncryption/src/AwsPkcs11Encryptor.php', // Because of Pkcs11
        // Skip rules
        ClassPropertyAssignToConstructorPromotionRector::class,
        CountOnNullRector::class,
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

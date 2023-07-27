<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyQuality\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff;
use EonX\EasyQuality\Sniffs\Attributes\SortAttributesAlphabeticallySniff;
use EonX\EasyQuality\Sniffs\Attributes\SortedApiResourceOperationKeysSniff;
use EonX\EasyQuality\Sniffs\Classes\AvoidPublicPropertiesSniff;
use EonX\EasyQuality\Sniffs\Classes\MakeClassAbstractSniff;
use EonX\EasyQuality\Sniffs\Classes\StrictDeclarationFormatSniff;
use EonX\EasyQuality\Sniffs\Constants\DisallowApplicationConstantAndEnumUsageInTestAssertBlock;
use EonX\EasyQuality\Sniffs\ControlStructures\ArrangeActAssertSniff;
use EonX\EasyQuality\Sniffs\ControlStructures\LinebreakAfterEqualsSignSniff;
use EonX\EasyQuality\Sniffs\ControlStructures\UseYieldInsteadOfReturnSniff;
use EonX\EasyQuality\Sniffs\Functions\DisallowNonNullDefaultValueSniff;
use EonX\EasyQuality\ValueObject\EasyQualitySetList;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\PSR12\Sniffs\Files\FileHeaderSniff;
use PhpCsFixer\Fixer\ClassUsage\DateTimeImmutableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAfterConstructFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedGlobalFunctionsSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(EasyQualitySetList::ECS);

    $ecsConfig->parallel(maxNumberOfProcess: 2, jobSize: 1);

    $ecsConfig->paths([
        __DIR__ . '/../monorepo-builder.php',
        __DIR__ . '/../packages',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);

    $ecsConfig->skip([
        // Skip entire files or directories
        'packages/*/tests/var/*', // Symfony cache files
        'packages/*/var/*', // Symfony cache files
        'packages/EasyApiPlatform/tests/Fixtures/app/var', // It is an Api Platform test app

        // Skip rules
        SingleSpaceAfterConstructFixer::class => null,
        AlphabeticallySortedArrayKeysSniff::class => [
            'packages/*/src/Bridge/Laravel/config/*',
            'packages/*/tests/*',
            'packages/EasySwoole/src/Runtime/EasySwooleRuntime.php',
            'packages/EasyUtils/src/CreditCard/CreditCardNumberValidator.php',
            'packages/EasyWebhook/src/Bridge/Laravel/EasyWebhookServiceProvider.php',
            'quality/ecs.php',
            'quality/rector.php',
        ],
        AvoidPublicPropertiesSniff::class => null,
        BlankLineAfterOpeningTagFixer::class => null,
        DateTimeImmutableFixer::class => null,
        DisallowMixedTypeHintSniff::class => null,
        DisallowNonNullDefaultValueSniff::class => null,
        FullyQualifiedGlobalFunctionsSniff::class => [
            'packages/*/src/Bridge/Symfony/Resources/config/*',
            'packages/EasyApiPlatform/tests/Fixtures/app/config/packages/*',
        ],
        LineLengthSniff::class . '.MaxExceeded' => [
            'packages/*/src/Bridge/BridgeConstantsInterface.php',
            'packages/EasySecurity/src/Bridge/Laravel/EasySecurityServiceProvider.php',
        ],
        MethodChainingNewlineFixer::class => [
            'packages/*/src/Bridge/Symfony/DependencyInjection/Configuration.php',
        ],
        PhpdocAlignFixer::class => [
            'packages/EasyUtils/src/Interfaces/MathInterface.php',
            'packages/EasyUtils/src/Math/Math.php',
        ],
        PropertyTypeHintSniff::class . '.MissingNativeTypeHint' => [
            'packages/*/src/Bridge/Symfony/Validator/Constraints/*',
            'packages/*/tests/Stubs/Model/*',
        ],
        PropertyTypeHintSniff::class . '.MissingTraversableTypeHintSpecification' => null,
        PropertyTypeHintSniff::class . '.UselessAnnotation' => [
            'packages/*/tests/Stubs/Model/*',
        ],
        StaticClosureSniff::class => [
            'packages/*/tests/*',
        ],
        StrictDeclarationFormatSniff::class => null,
    ]);

    $ecsConfig->rules([
        AvoidPublicPropertiesSniff::class,
        DateTimeImmutableFixer::class,
        DisallowApplicationConstantAndEnumUsageInTestAssertBlock::class,
        FileHeaderSniff::class,
        LinebreakAfterEqualsSignSniff::class,
        MethodChainingNewlineFixer::class,
        SortAttributesAlphabeticallySniff::class,
        SortedApiResourceOperationKeysSniff::class,
        StandaloneLinePromotedPropertyFixer::class,
        StaticClosureSniff::class,
        UselessConstantTypeHintSniff::class,
    ]);

    $ecsConfig->ruleWithConfiguration(AlphabeticallySortedArrayKeysSniff::class, [
        'skipPatterns' => [\T_ATTRIBUTE => []],
    ]);
    $ecsConfig->ruleWithConfiguration(ArrangeActAssertSniff::class, [
        'testNamespace' => 'Test',
    ]);
    $ecsConfig->ruleWithConfiguration(DocCommentSpacingSniff::class, [
        'annotationsGroups' => [
            '@Annotation',
            '@Target',
            '@template',
            '@phpstan-template',
            '@param',
            '@phpstan-param',
            '@psalm-param',
            '@return',
            '@phpstan-return',
            '@throws',
            '@deprecated',
            '@method',
            '@property',
            '@noinspection',
            '@see',
            '@SuppressWarnings',
            '@var',
            '@phpstan-var',
            '@author',
        ],
        'linesCountBetweenAnnotationsGroups' => 1,
    ]);
    $ecsConfig->ruleWithConfiguration(MakeClassAbstractSniff::class, [
        'applyTo' => [
            [
                'namespace' => '/^Test/',
                'patterns' => [
                    '/.*TestCase$/',
                ],
            ],
        ],
    ]);
    $ecsConfig->ruleWithConfiguration(UseYieldInsteadOfReturnSniff::class, [
        'applyTo' => [
            [
                'namespace' => '/^Test/',
                'patterns' => [
                    '/^provide[A-Z]*/',
                ],
            ],
        ],
    ]);
};

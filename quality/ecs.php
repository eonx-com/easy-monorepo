<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyQuality\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff;
use EonX\EasyQuality\Sniffs\Attributes\SortAttributesAlphabeticallySniff;
use EonX\EasyQuality\Sniffs\Attributes\SortedApiResourceOperationKeysSniff;
use EonX\EasyQuality\Sniffs\Classes\AvoidPublicPropertiesSniff;
use EonX\EasyQuality\Sniffs\Classes\MakeClassAbstractSniff;
use EonX\EasyQuality\Sniffs\Constants\DisallowApplicationConstantAndEnumUsageInTestAssertBlock;
use EonX\EasyQuality\Sniffs\ControlStructures\ArrangeActAssertSniff;
use EonX\EasyQuality\Sniffs\ControlStructures\LinebreakAfterEqualsSignSniff;
use EonX\EasyQuality\Sniffs\ControlStructures\UseYieldInsteadOfReturnSniff;
use EonX\EasyQuality\Sniffs\Functions\DisallowNonNullDefaultValueSniff;
use EonX\EasyQuality\ValueObject\EasyQualitySetList;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PhpCsFixer\Fixer\ClassUsage\DateTimeImmutableFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAfterConstructFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedClassNameInAnnotationSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedGlobalFunctionsSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseSpacingSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->cacheDirectory(__DIR__ . '/var/cache/ecs');

    $ecsConfig->import(EasyQualitySetList::ECS);

    $ecsConfig->parallel(maxNumberOfProcess: 2, jobSize: 1);

    $ecsConfig->paths([
        __DIR__ . '/../bin',
        __DIR__ . '/../config',
        __DIR__ . '/../monorepo',
        __DIR__ . '/../monorepo-builder.php',
        __DIR__ . '/../packages',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);

    $ecsConfig->skip([
        // Skip entire files or directories
        'packages/*/var/*', // Cache files
        'packages/*/vendor/*', // Composer dependencies installed locally for development and testing

        // Skip rules
        AlphabeticallySortedArrayKeysSniff::class => [
            'packages/*/src/Bridge/Laravel/config/*',
            'packages/*/tests/*',
            'packages/EasySwoole/src/Runtime/EasySwooleRuntime.php',
            'packages/EasyUtils/src/CreditCard/CreditCardNumberValidator.php',
            'packages/EasyWebhook/src/Bridge/Laravel/EasyWebhookServiceProvider.php',
            'quality/ecs.php',
            'quality/rector.php',
        ],
        AvoidPublicPropertiesSniff::class => [
            'packages/*/src/Bridge/Symfony/Validator/Constraints/*',
            'packages/*/tests/Stubs/Model/*',
            'packages/*/tests/*/Fixtures/*/ApiResource/*',
            'packages/*/tests/*/Fixtures/*/DataTransferObject/*',
            'packages/EasyWebhook/src/Bridge/Laravel/Jobs/SendWebhookJob.php',
        ],
        BlankLineAfterOpeningTagFixer::class => null,
        DateTimeImmutableFixer::class => null,
        DisallowMixedTypeHintSniff::class => [
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/*',
        ],
        DisallowNonNullDefaultValueSniff::class => null,
        FullyQualifiedGlobalFunctionsSniff::class => [
            'config/monorepo_services.php',
            'packages/*/config/*',
        ],
        FullyQualifiedClassNameInAnnotationSniff::class => [
            'packages/EasyTest/src/Traits/ContainerServiceTrait.php',
            'packages/EasyTest/src/Traits/DatabaseEntityTrait.php',
        ],
        LineLengthSniff::class . '.MaxExceeded' => [
            'packages/*/src/Bridge/BridgeConstantsInterface.php',
            'packages/EasySecurity/src/Bridge/Laravel/EasySecurityServiceProvider.php',
        ],
        MethodChainingNewlineFixer::class => [
            'packages/*/definition.php',
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
        SingleSpaceAfterConstructFixer::class => null,
        StaticClosureSniff::class => [
            'packages/*/tests/*',
        ],
    ]);

    $ecsConfig->rules([
        AvoidPublicPropertiesSniff::class,
        DateTimeImmutableFixer::class,
        DisallowApplicationConstantAndEnumUsageInTestAssertBlock::class,
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
            '@group',
            '@dataProvider',
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
    $ecsConfig->ruleWithConfiguration(TrailingCommaInMultilineFixer::class, [
        'elements' => [
            TrailingCommaInMultilineFixer::ELEMENTS_ARRAYS,
            TrailingCommaInMultilineFixer::ELEMENTS_PARAMETERS,
        ],
    ]);
    $ecsConfig->ruleWithConfiguration(UseSpacingSniff::class, [
        'linesCountBetweenUseTypes' => 1,
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

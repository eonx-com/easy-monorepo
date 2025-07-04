<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyQuality\Helper\ParallelSettingsHelper;
use EonX\EasyQuality\Sniffs\Arrays\AlphabeticallySortedArraySniff;
use EonX\EasyQuality\Sniffs\Attributes\SortAttributesAlphabeticallySniff;
use EonX\EasyQuality\Sniffs\Attributes\SortedApiResourceOperationKeysSniff;
use EonX\EasyQuality\Sniffs\Classes\AvoidPublicPropertiesSniff;
use EonX\EasyQuality\Sniffs\Classes\MakeClassAbstractSniff;
use EonX\EasyQuality\Sniffs\Constants\DisallowApplicationConstantAndEnumUsageInTestAssertBlock;
use EonX\EasyQuality\Sniffs\ControlStructures\ArrangeActAssertSniff;
use EonX\EasyQuality\Sniffs\ControlStructures\LinebreakAfterEqualsSignSniff;
use EonX\EasyQuality\Sniffs\ControlStructures\UseYieldInsteadOfReturnSniff;
use EonX\EasyQuality\Sniffs\Functions\DisallowNonNullDefaultValueSniff;
use EonX\EasyQuality\Sniffs\Namespaces\Psr4Sniff;
use EonX\EasyQuality\ValueObject\EasyQualitySetList;
use PhpCsFixer\Fixer\Basic\BracesFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\ClassUsage\DateTimeImmutableFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAfterConstructFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
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

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/../bin',
        __DIR__ . '/../config',
        __DIR__ . '/../monorepo',
        __DIR__ . '/../monorepo-builder.php',
        __DIR__ . '/../packages',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ])
    ->withParallel(
        ParallelSettingsHelper::getTimeoutSeconds(),
        ParallelSettingsHelper::getMaxNumberOfProcess(),
        ParallelSettingsHelper::getJobSize()
    )
    ->withCache(__DIR__ . '/var/cache/ecs')
    ->withSets([
        EasyQualitySetList::ECS,
    ])
    ->withSkip([
        AlphabeticallySortedArraySniff::class => [
            'packages/*/laravel/config/*',
            'packages/*/tests/*',
            'packages/EasySwoole/src/Common/Runtime/EasySwooleRuntime.php',
            'packages/EasyUtils/src/CreditCard/Validator/CreditCardNumberValidator.php',
            'packages/EasyWebhook/laravel/EasyWebhookServiceProvider.php',
        ],
        AvoidPublicPropertiesSniff::class => [
            'packages/*/src/*/Constraint/*',
            'packages/*/tests/Stub/Model/*',
            'packages/*/tests/Fixture/*/ApiResource/*',
            'packages/*/tests/Fixture/*/DataTransferObject/*',
            'packages/EasySwoole/src/Common/ValueObject/SwooleTableColumnDefinition.php',
            'packages/EasyWebhook/laravel/Jobs/SendWebhookJob.php',
        ],
        BlankLineAfterOpeningTagFixer::class => null,
        BracesFixer::class => [
            'packages/EasyTest/.phpstorm.meta.php',
        ],
        ClassDefinitionFixer::class => [
            'packages/EasyDecision/src/Configurator/AbstractNameRestrictedDecisionConfigurator.php',
            'packages/EasyDecision/src/Configurator/AbstractTypeRestrictedDecisionConfigurator.php',
            'packages/EasyLock/src/Common/Exception/LockAcquiringException.php',
        ],
        DateTimeImmutableFixer::class => null,
        DisallowMixedTypeHintSniff::class => [
            'packages/EasyBankFiles/src/Parsing/Common/Converter/XmlConverter.php',
            'packages/EasySecurity/src/SymfonySecurity/Voter/*',
        ],
        DisallowNonNullDefaultValueSniff::class => null,
        FinalClassFixer::class => [
            'packages/EasyActivity/tests/Fixture/app/src/Entity/Type.php',
            'packages/EasyApiPlatform/tests/Fixture/app/src/AdvancedSearchFilter/ApiResource/EmbeddableDummy.php',
            'packages/EasySecurity/src/Common/Context/SecurityContext.php',
            'packages/EasyServerless/src/Aws/Runtime/ServerlessSymfonyRuntime.php',
            'packages/EasyTest/src/InvalidData/Maker/InvalidDataMaker.php',
        ],
        FullyQualifiedClassNameInAnnotationSniff::class => [
            'packages/EasyTest/src/Common/Trait/ContainerServiceTrait.php',
            'packages/EasyTest/src/Common/Trait/DatabaseEntityTrait.php',
        ],
        FullyQualifiedGlobalFunctionsSniff::class => [
            'config/monorepo_services.php',
            'packages/*/config/*',
            'packages/EasyTest/.phpstorm.meta.php',
        ],
        MethodChainingNewlineFixer::class => [
            'packages/*/definition.php',
        ],
        NoExtraBlankLinesFixer::class => [
            'packages/EasyTest/.phpstorm.meta.php',
        ],
        'packages/*/var/*',
        'packages/*/vendor/*',
        PhpdocAlignFixer::class => [
            'packages/EasyUtils/src/Math/Helper/MathHelperInterface.php',
            'packages/EasyUtils/src/Math/Helper/MathHelper.php',
        ],
        PropertyTypeHintSniff::class . '.MissingNativeTypeHint' => [
            'packages/*/src/*/Constraint/*',
            'packages/*/tests/Stub/Model/*',
        ],
        PropertyTypeHintSniff::class . '.MissingTraversableTypeHintSpecification' => null,
        PropertyTypeHintSniff::class . '.UselessAnnotation' => [
            'packages/*/tests/Stub/Model/*',
        ],
        Psr4Sniff::class => [
            'quality/tests/*',
        ],
        SingleSpaceAfterConstructFixer::class => null,
        SingleSpaceAroundConstructFixer::class => null,
        StaticClosureSniff::class => [
            'packages/*/tests/*',
        ],
    ])
    ->withRules([
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
    ])
    ->withConfiguredRule(AlphabeticallySortedArraySniff::class, [
        'skipPatterns' => [\T_ATTRIBUTE => []],
    ])
    ->withConfiguredRule(ArrangeActAssertSniff::class, [
        'testNamespace' => 'Test',
    ])
    ->withConfiguredRule(DocCommentSpacingSniff::class, [
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
    ])
    ->withConfiguredRule(MakeClassAbstractSniff::class, [
        'applyTo' => [
            [
                'namespace' => '/^Test/',
                'patterns' => [
                    '/.*TestCase$/',
                ],
            ],
        ],
    ])
    ->withConfiguredRule(TrailingCommaInMultilineFixer::class, [
        'elements' => [
            TrailingCommaInMultilineFixer::ELEMENTS_ARRAYS,
            TrailingCommaInMultilineFixer::ELEMENTS_PARAMETERS,
        ],
    ])
    ->withConfiguredRule(UseSpacingSniff::class, [
        'linesCountBetweenUseTypes' => 1,
    ])
    ->withConfiguredRule(UseYieldInsteadOfReturnSniff::class, [
        'applyTo' => [
            [
                'namespace' => '/^Test/',
                'patterns' => [
                    '/^provide[A-Z]*/',
                ],
            ],
        ],
    ]);

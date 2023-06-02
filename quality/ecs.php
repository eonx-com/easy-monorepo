<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyQuality\Sniffs\ControlStructures\NoNotOperatorSniff;
use EonX\EasyQuality\Sniffs\Namespaces\Psr4Sniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\PSR12\Sniffs\Files\FileHeaderSniff;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAfterConstructFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff;
use SlevomatCodingStandard\Sniffs\Functions\DisallowTrailingCommaInCallSniff;
use SlevomatCodingStandard\Sniffs\Functions\DisallowTrailingCommaInClosureUseSniff;
use SlevomatCodingStandard\Sniffs\Functions\DisallowTrailingCommaInDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Functions\RequireTrailingCommaInDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedClassNameInAnnotationSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedGlobalConstantsSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedGlobalFunctionsSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([
        SetList::ARRAY,
        SetList::CLEAN_CODE,
        SetList::COMMON,
        SetList::PSR_12,
    ]);

    $ecsConfig->parallel();
    $ecsConfig->cacheDirectory(__DIR__ . '/var/cache/ecs');

    $ecsConfig->paths([
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/../monorepo-builder.php',
        __DIR__ . '/../packages',
    ]);

    $ecsConfig->skip([
        'packages/*/var/*php',
        '*/vendor/*.php',
        'packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Filter/VirtualSearchFilter.php',

        NotOperatorWithSuccessorSpaceFixer::class,
        CastSpacesFixer::class,
        OrderedClassElementsFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
        PhpdocVarWithoutNameFixer::class,
        PhpUnitStrictFixer::class,
        BlankLineAfterOpeningTagFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        RemoveUselessDefaultCommentFixer::class,

        FullyQualifiedGlobalFunctionsSniff::class => [
            'config/*',
            'src/*/Config/*',
        ],

        MethodChainingNewlineFixer::class => [
            // bug, to be fixed in symplify
            '*/Configuration.php',
            'packages/EasyCore/tests/Doctrine/DBAL/Types/DateTimeMicrosecondsTypeTest.php',
        ],
        AssignmentInConditionSniff::class => [
            'packages/EasyCore/src/Csv/FromFileCsvContentsProvider.php',
            'packages/EasyUtils/src/Csv/FromFileCsvContentsProvider.php',
        ],
        LineLengthSniff::class . '.MaxExceeded' => [
            'packages/EasyEncryption/src/Bridge/BridgeConstantsInterface.php',
            'packages/EasyEncryption/src/Bridge/Symfony/Resources/config/aws_pkcs11_encryptor.php',
            'packages/EasyErrorHandler/src/Bridge/BridgeConstantsInterface.php',
        ],
        MethodChainingIndentationFixer::class => ['*/Configuration.php'],
        NullTypeHintOnLastPositionSniff::class . '.NullTypeHintNotOnLastPosition',
        ParameterTypeHintSniff::class . '.MissingAnyTypeHint',
        ReturnTypeHintSniff::class . '.MissingTraversableTypeHintSpecification',
        ParameterTypeHintSniff::class . '.MissingTraversableTypeHintSpecification',
        ReturnTypeHintSniff::class . '.MissingAnyTypeHint',
        ParameterTypeHintSniff::class . '.MissingNativeTypeHint' => [
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/CacheConfigCommand.php',
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/ClearConfigCommand.php',
            'packages/EasyCore/src/Bridge/Symfony/Serializer/TrimStringsDenormalizer.php',
            'packages/EasyLogging/src/Logger.php',
            'packages/EasyApiToken/src/External/Auth0JwtDriver.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            'packages/EasyCore/tests/Bridge/Symfony/Stubs/NormalizerStub.php',
            'packages/EasyCore/tests/Stubs/LockStub.php',
            'packages/EasySsm/tests/Stubs/BaseSsmClientStub.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        ParameterTypeHintSniff::class . '.UselessAnnotation' => [
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/CacheConfigCommand.php',
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/ClearConfigCommand.php',
            'packages/EasyCore/src/Bridge/Symfony/Serializer/TrimStringsDenormalizer.php',
            'packages/EasyLogging/src/Logger.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            'packages/EasyCore/tests/Bridge/Symfony/Stubs/NormalizerStub.php',
            'packages/EasyCore/tests/Stubs/LockStub.php',
            'packages/EasySsm/tests/Stubs/BaseSsmClientStub.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        ReturnTypeHintSniff::class . '.MissingNativeTypeHint' => [
            'packages/EasyRepository/src/Implementations/Illuminate/AbstractEloquentRepository.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasyRepository/src/Implementations/Doctrine/ORM/DoctrineOrmRepositoryTrait.php',
            'packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Routing/IriConverter.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        ReturnTypeHintSniff::class . '.UselessAnnotation' => [
            'packages/EasyRepository/src/Implementations/Illuminate/AbstractEloquentRepository.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasyRepository/src/Implementations/Doctrine/ORM/DoctrineOrmRepositoryTrait.php',
            'packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Routing/IriConverter.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        UselessVariableSniff::class . '.UselessVariable' => ['packages/EasySchedule/src/Schedule.php'],
        UnusedVariableSniff::class . '.UnusedVariable' => [
            __DIR__ .
            '/packages/EasyAsync/src/Bridge/Symfony/DependencyInjection/Compiler/' .
            'AddBatchMiddlewareToMessengerBusesPass.php',
        ],
        ReferenceThrowableOnlySniff::class . '.ReferencedGeneralException' => [
            'packages/EasyErrorHandler/src/Bridge/Laravel/ExceptionHandler.php',
            'packages/EasyErrorHandler/tests/Bridge/Laravel/ExceptionHandlerTest.php',
        ],
        ReturnAssignmentFixer::class => [
            'packages/EasyCore/src/Bridge/Symfony/Doctrine/EntityManagerResolver.php',
        ],
        SingleSpaceAfterConstructFixer::class,
    ]);

    $ecsConfig->rule(FileHeaderSniff::class);

    $ecsConfig->rule(FullyQualifiedClassNameInAnnotationSniff::class);
    $ecsConfig->rule(FullyQualifiedGlobalConstantsSniff::class);
    $ecsConfig->rule(FullyQualifiedGlobalFunctionsSniff::class);

    $ecsConfig->rule(MethodChainingNewlineFixer::class);

    $ecsConfig->ruleWithConfiguration(YodaStyleFixer::class, [
        'equal' => false,
        'identical' => false,
        'less_and_greater' => false,
    ]);

    $ecsConfig->rule(NoNotOperatorSniff::class);

    $ecsConfig->rule(Psr4Sniff::class);

    // symplify rules - see https://github.com/symplify/coding-standard/blob/master/docs/phpcs_fixer_fixers.md
    // arrays
    $ecsConfig->rule(StandaloneLineInMultilineArrayFixer::class);

    // annotations
    $ecsConfig->rule(ParamReturnAndVarTagMalformsFixer::class);

    // extra spaces
    $ecsConfig->rule(PhpdocTrimConsecutiveBlankLineSeparationFixer::class);
    $ecsConfig->rule(BinaryOperatorSpacesFixer::class);

    $ecsConfig->rule(VisibilityRequiredFixer::class);

    $ecsConfig->ruleWithConfiguration(ClassAttributesSeparationFixer::class, [
        'elements' => [
            'const' => 'one',
            'method' => 'one',
            'property' => 'one',
        ],
    ]);

    $ecsConfig->ruleWithConfiguration(LineLengthSniff::class, [
        'absoluteLineLimit' => 120,
        'ignoreComments' => true,
    ]);

    // trailing commas
    $ecsConfig->ruleWithConfiguration(DisallowTrailingCommaInCallSniff::class, [
        'onlySingleLine' => true,
    ]);
    //    $ecsConfig->rule(RequireTrailingCommaInCallSniff::class);
    $ecsConfig->ruleWithConfiguration(DisallowTrailingCommaInClosureUseSniff::class, [
        'onlySingleLine' => true,
    ]);
    //    $ecsConfig->rule(RequireTrailingCommaInClosureUseSniff::class);
    $ecsConfig->ruleWithConfiguration(DisallowTrailingCommaInDeclarationSniff::class, [
        'onlySingleLine' => true,
    ]);
    $ecsConfig->rule(RequireTrailingCommaInDeclarationSniff::class);
};

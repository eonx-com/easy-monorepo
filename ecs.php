<?php

declare(strict_types=1);

use EonX\EasyStandard\Sniffs\ControlStructures\NoElseSniff;
use EonX\EasyStandard\Sniffs\ControlStructures\NoNotOperatorSniff;
use EonX\EasyStandard\Sniffs\Namespaces\Psr4Sniff;
use PHP_CodeSniffer\Standards\PSR12\Sniffs\Files\FileHeaderSniff;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff;
use SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Commenting\RemoveSuperfluousDocBlockWhitespaceFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\CodingStandard\Fixer\Spacing\RemoveSpacingAroundModifierAndConstFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages',
        __DIR__ . '/changelog-linker.php',
        __DIR__ . '/monorepo-builder.php',
        __DIR__ . '/ecs.php',
    ]);

    $parameters->set(Option::EXCLUDE_PATHS, [
        'packages/*/var/*php',
        '*/vendor/*.php',
        __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Filter/VirtualSearchFilter.php',
        __DIR__ . '/packages/EasyStandard/src/Sniffs/Commenting/FunctionCommentSniff.php',
    ]);

    $parameters->set(Option::SETS, [
        SetList::COMMON,
        SetList::CLEAN_CODE,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PSR_12,
        SetList::DEAD_CODE,
        SetList::ARRAY,
    ]);

    $parameters->set(Option::SKIP, [
        NotOperatorWithSuccessorSpaceFixer::class => null,
        CastSpacesFixer::class => null,
        OrderedClassElementsFixer::class => null,
        NoSuperfluousPhpdocTagsFixer::class => null,
        PhpdocVarWithoutNameFixer::class => null,
        PhpUnitStrictFixer::class => null,
        BlankLineAfterOpeningTagFixer::class => null,

        MethodChainingIndentationFixer::class => ['*/Configuration.php'],

        MethodChainingNewlineFixer::class => [
            // bug, to be fixed in symplify
            '*/Configuration.php',
            __DIR__ . '/packages/EasyCore/tests/Doctrine/DBAL/Types/DateTimeMicrosecondsTypeTest.php',
        ],
        NullTypeHintOnLastPositionSniff::class . '.NullTypeHintNotOnLastPosition' => null,
        ParameterTypeHintSniff::class . '.MissingAnyTypeHint' => null,
        ReturnTypeHintSniff::class . '.MissingTraversableTypeHintSpecification' => null,
        ParameterTypeHintSniff::class . '.MissingTraversableTypeHintSpecification' => null,
        ReturnTypeHintSniff::class . '.MissingAnyTypeHint' => null,
        ParameterTypeHintSniff::class . '.MissingNativeTypeHint' => [
            __DIR__ . '/packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/CacheConfigCommand.php',
            __DIR__ . '/packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/ClearConfigCommand.php',
            __DIR__ . 'packages/EasyCore/src/Bridge/Symfony/Serializer/TrimStringsDenormalizer.php',
            __DIR__ . '/packages/EasyLogging/src/Logger.php',
            __DIR__ . '/packages/EasyApiToken/src/External/Auth0JwtDriver.php',
            __DIR__ . '/packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            __DIR__ . '/packages/EasyAsync/src/Helpers/PropertyHelper.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/ContextAuthenticator.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            __DIR__ . 'packages/EasyCore/tests/Bridge/Symfony/Stubs/NormalizerStub.php',
            __DIR__ . '/packages/EasyCore/tests/Stubs/LockStub.php',
            __DIR__ . '/packages/EasyStandard/src/Sniffs',
            __DIR__ . '/packages/EasySsm/tests/Stubs/BaseSsmClientStub.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            __DIR__ . '/packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        ParameterTypeHintSniff::class . '.UselessAnnotation' => [
            __DIR__ . '/packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/CacheConfigCommand.php',
            __DIR__ . '/packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/ClearConfigCommand.php',
            __DIR__ . 'packages/EasyCore/src/Bridge/Symfony/Serializer/TrimStringsDenormalizer.php',
            __DIR__ . '/packages/EasyLogging/src/Logger.php',
            __DIR__ . '/packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            __DIR__ . '/packages/EasyAsync/src/Helpers/PropertyHelper.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/ContextAuthenticator.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            __DIR__ . 'packages/EasyCore/tests/Bridge/Symfony/Stubs/NormalizerStub.php',
            __DIR__ . '/packages/EasyCore/tests/Stubs/LockStub.php',
            __DIR__ . '/packages/EasyStandard/src/Sniffs',
            __DIR__ . '/packages/EasySsm/tests/Stubs/BaseSsmClientStub.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            __DIR__ . '/packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        ReturnTypeHintSniff::class . '.MissingNativeTypeHint' => [
            __DIR__ . '/packages/EasyRepository/src/Implementations/Illuminate/AbstractEloquentRepository.php',
            __DIR__ . '/packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            __DIR__ . '/packages/EasyRepository/src/Implementations/Doctrine/ORM/DoctrineOrmRepositoryTrait.php',
            __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Routing/IriConverter.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            __DIR__ . '/packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        ReturnTypeHintSniff::class . '.UselessAnnotation' => [
            __DIR__ . '/packages/EasyRepository/src/Implementations/Illuminate/AbstractEloquentRepository.php',
            __DIR__ . '/packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            __DIR__ . '/packages/EasyRepository/src/Implementations/Doctrine/ORM/DoctrineOrmRepositoryTrait.php',
            __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Routing/IriConverter.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            __DIR__ . '/packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            __DIR__ . '/packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            __DIR__ . '/packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php',
        ],
        UselessVariableSniff::class . '.UselessVariable' => [__DIR__ . '/packages/EasySchedule/src/Schedule.php'],
        UnusedPrivateElementsSniff::class . '.WriteOnlyProperty' => [
            __DIR__ . '/packages/EasyErrorHandler/src/Bridge/Laravel/Handler/Handler.php',
        ],
        ReferenceThrowableOnlySniff::class . '.ReferencedGeneralException' => [
            __DIR__ . '/packages/EasyErrorHandler/src/Bridge/Laravel/ExceptionHandler.php',
            __DIR__ . '/packages/EasyErrorHandler/tests/Bridge/Laravel/ExceptionHandlerTest.php',
        ],
        ReturnAssignmentFixer::class => [
            __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/Doctrine/EntityManagerResolver.php',
        ],
    ]);

    $services = $containerConfigurator->services();
    $services->set(FileHeaderSniff::class);

    $services->set(MethodChainingNewlineFixer::class);

    $services->set(YodaStyleFixer::class)
        ->call('configure', [
            [
                'equal' => false,
                'identical' => false,
                'less_and_greater' => false,
            ],
        ]);

    $services->set(NoElseSniff::class);
    $services->set(NoNotOperatorSniff::class);
    $services->set(Psr4Sniff::class);

    // sypmlify rules - see https://github.com/symplify/coding-standard/blob/master/docs/phpcs_fixer_fixers.md
    // arrays
    $services->set(ArrayOpenerNewlineFixer::class);
    $services->set(StandaloneLineInMultilineArrayFixer::class);

    // annotations
    $services->set(ParamReturnAndVarTagMalformsFixer::class);

    // extra spaces
    $services->set(RemoveSuperfluousDocBlockWhitespaceFixer::class);
    $services->set(RemoveSpacingAroundModifierAndConstFixer::class);

    // line length 120
    $services->set(LineLengthFixer::class);
};

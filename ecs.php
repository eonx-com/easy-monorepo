<?php

declare(strict_types=1);

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
use SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $containerConfigurator->import(SetList::ARRAY);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::PSR_12);

    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::PARALLEL_JOB_SIZE, 1);
    $parameters->set(Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, 2);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/changelog-linker.php',
        __DIR__ . '/ecs.php',
        __DIR__ . '/monorepo-builder.php',
        __DIR__ . '/packages',
    ]);

    $parameters->set(Option::SKIP, [
        'packages/*/var/*php',
        '*/vendor/*.php',
        __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Filter/VirtualSearchFilter.php',

        NotOperatorWithSuccessorSpaceFixer::class,
        CastSpacesFixer::class,
        OrderedClassElementsFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
        PhpdocVarWithoutNameFixer::class,
        PhpUnitStrictFixer::class,
        BlankLineAfterOpeningTagFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        RemoveUselessDefaultCommentFixer::class,

        MethodChainingNewlineFixer::class => [
            // bug, to be fixed in symplify
            '*/Configuration.php',
            __DIR__ . '/packages/EasyCore/tests/Doctrine/DBAL/Types/DateTimeMicrosecondsTypeTest.php',
        ],
        AssignmentInConditionSniff::class => [
            __DIR__ . '/packages/EasyCore/src/Csv\FromFileCsvContentsProvider.php',
        ],
        MethodChainingIndentationFixer::class => ['*/Configuration.php'],
        NullTypeHintOnLastPositionSniff::class . '.NullTypeHintNotOnLastPosition',
        ParameterTypeHintSniff::class . '.MissingAnyTypeHint',
        ReturnTypeHintSniff::class . '.MissingTraversableTypeHintSpecification',
        ParameterTypeHintSniff::class . '.MissingTraversableTypeHintSpecification',
        ReturnTypeHintSniff::class . '.MissingAnyTypeHint',
        ParameterTypeHintSniff::class . '.MissingNativeTypeHint' => [
            __DIR__ . '/packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/CacheConfigCommand.php',
            __DIR__ . '/packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/ClearConfigCommand.php',
            __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/Serializer/TrimStringsDenormalizer.php',
            __DIR__ . '/packages/EasyLogging/src/Logger.php',
            __DIR__ . '/packages/EasyApiToken/src/External/Auth0JwtDriver.php',
            __DIR__ . '/packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/ContextAuthenticator.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            __DIR__ . '/packages/EasyCore/tests/Bridge/Symfony/Stubs/NormalizerStub.php',
            __DIR__ . '/packages/EasyCore/tests/Stubs/LockStub.php',
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
            __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/Serializer/TrimStringsDenormalizer.php',
            __DIR__ . '/packages/EasyLogging/src/Logger.php',
            __DIR__ . '/packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/ContextAuthenticator.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            __DIR__ . '/packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            __DIR__ . '/packages/EasyCore/tests/Bridge/Symfony/Stubs/NormalizerStub.php',
            __DIR__ . '/packages/EasyCore/tests/Stubs/LockStub.php',
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
        UnusedVariableSniff::class . '.UnusedVariable' => [
            __DIR__ .
            '/packages/EasyAsync/src/Bridge/Symfony/DependencyInjection/Compiler/' .
            'AddBatchMiddlewareToMessengerBusesPass.php',
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

    $services->set(NoNotOperatorSniff::class);
    $services->set(Psr4Sniff::class);

    // symplify rules - see https://github.com/symplify/coding-standard/blob/master/docs/phpcs_fixer_fixers.md
    // arrays
    $services->set(StandaloneLineInMultilineArrayFixer::class);

    // annotations
    $services->set(ParamReturnAndVarTagMalformsFixer::class);

    // extra spaces
    $services->set(PhpdocTrimConsecutiveBlankLineSeparationFixer::class);
    $services->set(BinaryOperatorSpacesFixer::class);

    $services->set(VisibilityRequiredFixer::class);

    $services->set(ClassAttributesSeparationFixer::class)
        ->call('configure', [
            [
                'elements' => [
                    'const' => 'one',
                    'method' => 'one',
                    'property' => 'one',
                ],
            ],
        ]);

    $services->set(LineLengthSniff::class)
        ->property('absoluteLineLimit', 120)
        ->property('ignoreComments', true);
};

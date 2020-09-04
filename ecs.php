<?php

declare(strict_types=1);

use EonX\EasyStandard\Sniffs\ControlStructures\NoElseSniff;
use EonX\EasyStandard\Sniffs\ControlStructures\NoNotOperatorSniff;
use EonX\EasyStandard\Sniffs\Namespaces\Psr4Sniff;
use PHP_CodeSniffer\Standards\PSR12\Sniffs\Files\FileHeaderSniff;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\Configuration\Option;
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
        '*/var/*.php',
        '*/vendor/*.php',
        __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Filter/VirtualSearchFilter.php',
        __DIR__ . '/packages/EasyStandard/src/Sniffs/Commenting/FunctionCommentSniff.php'
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
        CastSpacesFixer::class => null,
        OrderedClassElementsFixer::class => null,
        NoSuperfluousPhpdocTagsFixer::class => null,
        PhpdocVarWithoutNameFixer::class => null,
        PhpUnitStrictFixer::class => null,
        BlankLineAfterOpeningTagFixer::class => null,
        MethodChainingIndentationFixer::class => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff.NullTypeHintNotOnLastPosition' => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff.MissingAnyTypeHint' => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff.MissingTraversableTypeHintSpecification' => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff.MissingTraversableTypeHintSpecification' => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff.MissingAnyTypeHint' => null,
        'SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff.MissingNativeTypeHint' => [
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/CacheConfigCommand.php',
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/ClearConfigCommand.php',
            'packages/EasyLogging/src/Logger.php',
            'packages/EasyApiToken/src/External/Auth0JwtDriver.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasyAsync/src/Helpers/PropertyHelper.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/ContextAuthenticator.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            'packages/EasyCore/tests/Stubs/LockStub.php',
            'packages/EasyStandard/src/Sniffs/*',
            'packages/EasySsm/tests/Stubs/BaseSsmClientStub.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php'
        ],
        'SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff.UselessAnnotation' => [
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/CacheConfigCommand.php',
            'packages/EasyCore/src/Bridge/Laravel/Console/Commands/Lumen/ClearConfigCommand.php',
            'packages/EasyLogging/src/Logger.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasyAsync/src/Helpers/PropertyHelper.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/ContextAuthenticator.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/PermissionVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/RoleVoter.php',
            'packages/EasySecurity/src/Bridge/Symfony/Security/Voters/ProviderVoter.php',
            'packages/EasyCore/tests/Stubs/LockStub.php',
            'packages/EasyStandard/src/Sniffs/*',
            'packages/EasySsm/tests/Stubs/BaseSsmClientStub.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php'],
        'SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff.MissingNativeTypeHint' => ['packages/EasyRepository/src/Implementations/Illuminate/AbstractEloquentRepository.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasyRepository/src/Implementations/Doctrine/ORM/DoctrineOrmRepositoryTrait.php',
            'packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Routing/IriConverter.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php'
        ],
        'SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff.UselessAnnotation' => [
            'packages/EasyRepository/src/Implementations/Illuminate/AbstractEloquentRepository.php',
            'packages/EasyRepository/src/Interfaces/ObjectRepositoryInterface.php',
            'packages/EasyRepository/src/Implementations/Doctrine/ORM/DoctrineOrmRepositoryTrait.php',
            'packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Routing/IriConverter.php',
            'packages/EasyEventDispatcher/src/Bridge/Laravel/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Bridge/Symfony/EventDispatcher.php',
            'packages/EasyEventDispatcher/src/Interfaces/EventDispatcherInterface.php',
            'packages/EasyEventDispatcher/tests/Bridge/Laravel/Stubs/LaravelEventDispatcherStub.php',
            'packages/EasyEventDispatcher/tests/Bridge/Symfony/Stubs/SymfonyEventDispatcherStub.php',
            'packages/EasyWebhook/tests/Stubs/EventDispatcherStub.php'
        ],
        'SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff.UselessVariable' => [
            'packages/EasySchedule/src/Schedule.php'
        ],
        'SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff.WriteOnlyProperty' => [
            'packages/EasyErrorHandler/src/Bridge/Laravel/Handler/Handler.php'
        ],
        'SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff.ReferencedGeneralException' => [
            'packages/EasyErrorHandler/src/Bridge/Laravel/Handler/Handler.php',
            'packages/EasyErrorHandler/tests/Bridge/Laravel/Handler/HandlerTest.php',
        ]
    ]);

    $services = $containerConfigurator->services();

    $services->set(FileHeaderSniff::class);

    $services->set(YodaStyleFixer::class)
        ->call('configure', [[
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false
        ]]);

    $services->set(NoElseSniff::class);
    $services->set(NoNotOperatorSniff::class);
    $services->set(Psr4Sniff::class);
};

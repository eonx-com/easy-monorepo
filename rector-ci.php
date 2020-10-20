<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\ProjectType;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Set\ValueObject\SetList;
use Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PROJECT_TYPE, ProjectType::OPEN_SOURCE);

    $parameters->set(Option::PATHS, [__DIR__ . '/packages', __DIR__ . '/tests']);

    $parameters->set(Option::SETS, [
        SetList::CODE_QUALITY,
        // SetList::DEAD_CODE,
    ]);

    $parameters->set(Option::SKIP, [
        RemoveExtraParametersRector::class => [
            // abstract method trait magic
            __DIR__ . '/packages/EasyCore/src/Bridge/Symfony/ApiPlatform/Listeners/ResolveRequestAttributesListener.php',
        ],
        SimplifyBoolIdenticalTrueRector::class => [
            __DIR__ . '/packages/EasyStandard/src/Sniffs/Commenting/FunctionCommentSniff.php',
        ],
    ]);

    $parameters->set(Option::EXCLUDE_PATHS, [
        __DIR__ . '/packages/EasyStandard/src/Sniffs/Commenting/FunctionCommentSniff.php',
    ]);

    $parameters->set(Option::EXCLUDE_RECTORS, [
        // resolve later - not sure about this
        UseInterfaceOverImplementationInConstructorRector::class,
        // opinionated
        SimplifyBoolIdenticalTrueRector::class,
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [__DIR__ . '/tests/rector_bootstrap.php']);
};

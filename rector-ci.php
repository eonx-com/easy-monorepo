<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\ProjectType;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {

    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PROJECT_TYPE, ProjectType::OPEN_SOURCE);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages',
        __DIR__ . '/tests',
    ]);

    $parameters->set(Option::SETS, [
        SetList::DEAD_CODE,
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/tests/rector_bootstrap.php',
    ]);

    $parameters->set(Option::SKIP, [
        RemoveUnreachableStatementRector::class => [
            __DIR__ . '/packages/EasyBankFiles/tests/Parsers/Nai/ParserTest.php',
        ],
    ]);
};

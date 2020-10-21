<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\ProjectType;
use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Rector\Php74\Tests\Rector\Function_\ReservedFnFunctionRector\Fixture\f;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/packages/EasyStandard/config/rector-set.php');

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

    $parameters->set(Option::SKIP, [
        RemoveUnusedDoctrineEntityMethodAndPropertyRector::class => [
            __DIR__ . '/packages/EasyEntityChange/tests/Integration/Fixtures/ProvidedIdEntity.php',
            __DIR__ . '/packages/EasyEntityChange/tests/Integration/Fixtures/SimpleEntity.php',
        ],
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/tests/rector_bootstrap.php',
    ]);
};

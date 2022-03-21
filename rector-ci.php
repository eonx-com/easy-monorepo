<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Set\ValueObject\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {

    // get parameters
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages',
        __DIR__ . '/tests',
    ]);

    $containerConfigurator->import(SetList::DEAD_CODE);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/tests/rector_bootstrap.php',
    ]);

    $parameters->set(Option::SKIP, [
        RemoveUnreachableStatementRector::class => [
            __DIR__ . '/packages/EasyBankFiles/tests/Parsers/Nai/ParserTest.php',
        ],
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveUnusedPrivatePropertyRector::class,
    ]);

    $services = $containerConfigurator->services();
    $services->load('EonX\EasyQuality\Rector\\', __DIR__ . '/.quality/vendor/eonx-com/easy-quality/src/Rector')
        ->exclude([
            __DIR__ . '/.quality/vendor/eonx-com/easy-quality/src/Rector/PhpDocCommentRector.php',
            __DIR__ . '/.quality/vendor/eonx-com/easy-quality/src/Rector/SingleLineCommentRector.php',
            __DIR__ . '/.quality/vendor/eonx-com/easy-quality/src/Rector/PhpDocReturnForIterableRector.php',
            __DIR__ . '/.quality/vendor/eonx-com/easy-quality/src/Rector/ReturnArrayToYieldRector.php',
        ]);
};

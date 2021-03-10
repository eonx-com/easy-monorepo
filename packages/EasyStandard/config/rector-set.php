<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->load('EonX\EasyStandard\Rector\\', __DIR__ . '/../src/Rector')
        ->exclude([
            __DIR__ . '/../src/Rector/PhpDocCommentRector.php',
            __DIR__ . '/../src/Rector/SingleLineCommentRector.php',
        ]);
};

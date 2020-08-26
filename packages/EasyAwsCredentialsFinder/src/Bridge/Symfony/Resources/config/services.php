<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('EonX\EasyAwsCredentialsFinder\\', __DIR__ . '/../../../../../src')
        ->exclude([
            __DIR__ . '/../../../../../src/Bridge/*',
            __DIR__ . '/../../../../../src/AwsCredentials.php',
            __DIR__ . '/../../../../../src/AwsSsoAccessToken.php',
        ]);
};

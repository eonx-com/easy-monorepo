<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Aws\Ssm\SsmClient;
use EonX\EasySsm\Factories\ArrFactory;
use EonX\EasySsm\Factories\FilesystemFactory;
use EonX\EasySsm\Factories\ParametersHelperFactory;
use EonX\EasySsm\Factories\SsmClientFactory;
use EonX\EasySsm\Helpers\Arr;
use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Dotenv\SsmDotenv;
use EonX\EasySsm\Services\Dotenv\SsmDotenvInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->load('EonX\EasySsm\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/HttpKernel/*',
            __DIR__ . '/../src/Services/Aws/Data/*',
            __DIR__ . '/../src/Services/Dotenv/Data/*',
            __DIR__ . '/../src/Services/Dotenv/Loaders/*',
            __DIR__ . '/../src/Services/Parameters/Data/*',
        ]);

    $services->set(Arr::class)
        ->factory([service(ArrFactory::class), 'create']);

    $services->set(Filesystem::class)
        ->factory([service(FilesystemFactory::class), 'create']);

    $services->set(Parameters::class)
        ->factory([service(ParametersHelperFactory::class), 'create']);

    $services->set(SsmClient::class)
        ->factory([service(SsmClientFactory::class), 'create']);

    $services->set(SsmDotenvInterface::class, SsmDotenv::class)
        ->public();

    $services->set(OutputInterface::class, ConsoleOutput::class);
};

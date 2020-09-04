<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('authors_to_ignore', ['natepage']);

    $parameters->set('repository_url', 'https://github.com/eonx-com/easy-monorepo');
};

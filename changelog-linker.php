<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\ChangelogLinker\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTHORS_TO_IGNORE, ['natepage']);

    $parameters->set(Option::REPOSITORY_URL, 'https://github.com/eonx-com/easy-monorepo');
};

<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symplify\ChangelogLinker\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTHORS_TO_IGNORE, ['natepage']);

    $parameters->set(Option::REPOSITORY_URL, 'https://github.com/eonx-com/easy-monorepo');
};

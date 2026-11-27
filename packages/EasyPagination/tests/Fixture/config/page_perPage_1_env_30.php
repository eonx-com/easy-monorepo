<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()->set('env(PAGINATION_PER_PAGE_DEFAULT)', '30');
    $containerConfigurator->extension('easy_pagination', [
        'pagination' => [
            'per_page_default' => '%env(int:PAGINATION_PER_PAGE_DEFAULT)%',
        ],
    ]);
};

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_pagination', [
        'pagination' => [
            'page_attribute' => 'page',
            'page_default' => 0,
            'per_page_attribute' => 'perPage',
            'per_page_default' => 15,
        ],
    ]);
};

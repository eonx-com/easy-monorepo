<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyPaginationConfig;

return static function (EasyPaginationConfig $easyPaginationConfig): void {
    $easyPaginationConfig->pagination()
        ->pageAttribute('page')
        ->pageDefault(1)
        ->perPageAttribute('perPage')
        ->perPageDefault(15);
};

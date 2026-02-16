<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'framework' => [
        'validation' => true,
        'secret' => 'some-secret',
        'test' => true,
    ],
]);

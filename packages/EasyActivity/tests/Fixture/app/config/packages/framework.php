<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'framework' => [
        'secret' => 'test-secret-for-testing',
        'test' => true,
    ],
]);

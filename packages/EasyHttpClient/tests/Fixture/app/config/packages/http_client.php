<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;

return App::config([
    'framework' => [
        'http_client' => [
            'mock_response_factory' => TestResponseFactory::class,
        ],
    ],
]);

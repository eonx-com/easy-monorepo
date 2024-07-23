<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig->httpClient()
        ->mockResponseFactory(TestResponseFactory::class);
};

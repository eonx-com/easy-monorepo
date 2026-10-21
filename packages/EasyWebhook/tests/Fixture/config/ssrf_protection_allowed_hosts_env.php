<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_webhook', [
        // Request limits are on by default in 7.x and would wrap the SSRF client, keep it outermost for the assertions
        'request_limits' => [
            'enabled' => false,
        ],
        'ssrf_protection' => [
            'allowed_hosts' => '%env(csv:EASY_WEBHOOK_TEST_SSRF_ALLOWED_HOSTS)%',
        ],
    ]);
};

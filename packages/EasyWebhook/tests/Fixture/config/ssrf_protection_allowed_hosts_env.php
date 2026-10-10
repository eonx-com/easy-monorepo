<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_webhook', [
        'ssrf_protection' => [
            'allowed_hosts' => '%env(csv:EASY_WEBHOOK_TEST_SSRF_ALLOWED_HOSTS)%',
        ],
    ]);
};

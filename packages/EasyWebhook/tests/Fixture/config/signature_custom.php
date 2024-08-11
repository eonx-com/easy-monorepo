<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyWebhookConfig;

return static function (EasyWebhookConfig $easyWebhookConfig): void {
    $easyWebhookConfig->signature()
        ->enabled(true)
        ->header('X-My-Header')
        ->secret('my-secret');
};

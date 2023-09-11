<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyHttpClientConfig;

return static function (EasyHttpClientConfig $easyHttpClientConfig): void {
    $easyHttpClientConfig
        ->decorateDefaultClient(true)
        ->decorateEasyWebhookClient(true)
        ->easyBugsnagEnabled(false)
        ->psrLoggerEnabled(false);
};

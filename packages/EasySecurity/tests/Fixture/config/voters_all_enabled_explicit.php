<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyBugsnagConfig;
use Symfony\Config\EasySecurityConfig;

return static function (EasySecurityConfig $easySecurityConfig, EasyBugsnagConfig $easyBugsnagConfig): void {
    $easySecurityConfig->voters()
        ->permissionVoter(true)
        ->providerVoter(true)
        ->roleVoter(true);

    $easyBugsnagConfig->apiKey('api-key');

    $easyBugsnagConfig->doctrineDbal()
        ->enabled(false);

    $easyBugsnagConfig->sensitiveDataSanitizer()
        ->enabled(false);
};

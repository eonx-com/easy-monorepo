<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyBugsnagConfig;

return static function (EasyBugsnagConfig $easyBugsnagConfig): void {
    $easyBugsnagConfig->apiKey('some-key');

    $easyBugsnagConfig->sensitiveDataSanitizer()
        ->enabled(false);
};

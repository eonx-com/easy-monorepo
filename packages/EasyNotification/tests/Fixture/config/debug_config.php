<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyNotificationConfig;

return static function (EasyNotificationConfig $easyNotificationConfig): void {
    $easyNotificationConfig->apiUrl('https://api.prod.v1.notifications.eonx.com');
};

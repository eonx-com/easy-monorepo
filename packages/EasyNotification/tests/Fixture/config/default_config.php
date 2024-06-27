<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyNotification\Provider\ConfigProviderInterface;
use EonX\EasyNotification\Tests\Stub\Provider\ConfigProviderStub;
use Symfony\Config\EasyNotificationConfig;

return static function (
    EasyNotificationConfig $easyNotificationConfig,
    ContainerConfigurator $containerConfigurator,
): void {
    $easyNotificationConfig->apiUrl('http://eonx.com');

    $services = $containerConfigurator->services();

    $services->set(ConfigProviderInterface::class, ConfigProviderStub::class)
        ->arg('$config', [
            'algorithm' => 'sha256',
            'apiKey' => 'my-api-key',
            'apiUrl' => 'http://eonx.com',
            'externalId' => 'ABCDE',
            'queueRegion' => 'ap-southeast-2',
            'queueUrl' => 'http://sqs.queue',
            'secret' => 'my-secret',
        ]);
};

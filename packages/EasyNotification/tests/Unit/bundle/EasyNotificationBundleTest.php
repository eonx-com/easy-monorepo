<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Bundle;

use EonX\EasyNotification\Client\NotificationClient;
use EonX\EasyNotification\Client\NotificationClientInterface;

final class EasyNotificationBundleTest extends AbstractSymfonyTestCase
{
    public function testSanityCheck(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/default_config.php'])->getContainer();

        self::assertInstanceOf(NotificationClient::class, $container->get(NotificationClientInterface::class));
    }
}

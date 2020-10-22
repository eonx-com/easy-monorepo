<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Bridge\Symfony;

use EonX\EasyNotification\Interfaces\NotificationClientInterface;
use EonX\EasyNotification\NotificationClient;

final class EasyNotificationBundleTest extends AbstractSymfonyTestCase
{
    public function testSanityCheck(): void
    {
        $kernel = $this->getKernel([__DIR__ . '/Fixtures/default_config.yaml']);
        $container = $kernel->getContainer();

        self::assertInstanceOf(NotificationClient::class, $container->get(NotificationClientInterface::class));
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Bridge\Laravel;

use EonX\EasyNotification\Config\CacheConfigFinder;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\NotificationClientInterface;
use EonX\EasyNotification\Interfaces\SubscribeInfoFinderInterface;
use EonX\EasyNotification\Subscribe\SubscribeInfoFinder;

final class EasyNotificationServiceProviderTest extends AbstractLaravelTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApp(static::$defaultConfig);

        self::assertInstanceOf(NotificationClientInterface::class, $app->get(NotificationClientInterface::class));
    }

    public function testSanityFinders(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(CacheConfigFinder::class, $app->get(ConfigFinderInterface::class));
        self::assertInstanceOf(SubscribeInfoFinder::class, $app->get(SubscribeInfoFinderInterface::class));
    }
}

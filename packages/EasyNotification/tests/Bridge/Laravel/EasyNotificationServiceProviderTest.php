<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Bridge\Laravel;

use EonX\EasyNotification\Config\CacheConfigFinder;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\NotificationClientInterface;

final class EasyNotificationServiceProviderTest extends AbstractLaravelTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApp(static::$defaultConfig);

        self::assertInstanceOf(NotificationClientInterface::class, $app->get(NotificationClientInterface::class));
    }

    public function testSanityCacheConfigFinder(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(CacheConfigFinder::class, $app->get(ConfigFinderInterface::class));
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Laravel;

use EonX\EasyNotification\Client\NotificationClientInterface;
use EonX\EasyNotification\Provider\CachedConfigProvider;
use EonX\EasyNotification\Provider\ConfigProviderInterface;
use EonX\EasyNotification\Provider\SubscribeInfoProvider;
use EonX\EasyNotification\Provider\SubscribeInfoProviderInterface;

final class EasyNotificationServiceProviderTest extends AbstractLaravelTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApp(static::$defaultConfig);

        self::assertInstanceOf(NotificationClientInterface::class, $app->get(NotificationClientInterface::class));
    }

    public function testSanityProviders(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(CachedConfigProvider::class, $app->get(ConfigProviderInterface::class));
        self::assertInstanceOf(SubscribeInfoProvider::class, $app->get(SubscribeInfoProviderInterface::class));
    }
}

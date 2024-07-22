<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Laravel;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurator\BasicsClientConfigurator;
use EonX\EasyBugsnag\Configurator\RuntimeVersionClientConfigurator;
use EonX\EasyBugsnag\Tracker\SessionTracker;

final class EasyBugsnagServiceProviderTest extends AbstractLaravelTestCase
{
    public function testDefaultConfiguratorsOff(): void
    {
        $app = $this->getApp([
            'easy-bugsnag' => [
                'api_key' => 'my-bugsnag-api-key',
                'use_default_configurators' => false,
            ],
        ]);

        self::assertFalse($app->has(BasicsClientConfigurator::class));
        self::assertFalse($app->has(RuntimeVersionClientConfigurator::class));
    }

    public function testDisableEntirePackage(): void
    {
        $app = $this->getApp([
            'easy-bugsnag' => [
                'enabled' => false,
                'api_key' => 'my-bugsnag-api-key',
            ],
        ]);

        self::assertFalse($app->has(Client::class));
    }

    public function testSanity(): void
    {
        $app = $this->getApp([
            'easy-bugsnag' => [
                'api_key' => 'my-bugsnag-api-key',
                'session_tracking' => true,
            ],
        ]);

        self::assertInstanceOf(Client::class, $app->get(Client::class));
        self::assertInstanceOf(SessionTracker::class, $app->get(SessionTracker::class));
    }
}

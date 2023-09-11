<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Configurators\RuntimeVersionConfigurator;
use EonX\EasyBugsnag\Session\SessionTracker;

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

        self::assertFalse($app->has(BasicsConfigurator::class));
        self::assertFalse($app->has(RuntimeVersionConfigurator::class));
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

<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyBugsnag\Session\SessionTracker;

final class EasyBugsnagServiceProviderTest extends AbstractLaravelTestCase
{
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

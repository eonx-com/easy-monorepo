<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Laravel;

use Bugsnag\Client;

/**
 * @coversNothing
 */
final class EasyBugsnagServiceProviderTest extends AbstractLaravelTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApp([
            'easy-bugsnag' => [
                'api_key' => 'my-bugsnag-api-key',
            ],
        ]);

        self::assertInstanceOf(Client::class, $app->get(Client::class));
    }
}

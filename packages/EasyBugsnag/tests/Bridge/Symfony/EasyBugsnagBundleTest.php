<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony;

use Bugsnag\Client;

final class EasyBugsnagBundleTest extends AbstractSymfonyTestCase
{
    public function testSanityCheck(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/default_config.yaml'])->getContainer();

        self::assertInstanceOf(Client::class, $container->get(Client::class));
    }
}

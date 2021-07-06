<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Configurators\RuntimeVersionConfigurator;
use EonX\EasyBugsnag\Session\SessionTracker;

final class EasyBugsnagBundleTest extends AbstractSymfonyTestCase
{
    public function testDefaultConfiguratorsOff(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/default_configurators_off.yaml'])->getContainer();

        self::assertFalse($container->has(BasicsConfigurator::class));
        self::assertFalse($container->has(RuntimeVersionConfigurator::class));
    }

    public function testSanityCheck(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/default_config.yaml'])->getContainer();

        self::assertInstanceOf(Client::class, $container->get(Client::class));
        self::assertInstanceOf(SessionTracker::class, $container->get(SessionTracker::class));
    }
}

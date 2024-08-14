<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Bundle;

use Bugsnag\Client;
use EonX\EasyBugsnag\Common\Configurator\BasicsClientConfigurator;
use EonX\EasyBugsnag\Common\Configurator\RuntimeVersionClientConfigurator;
use EonX\EasyBugsnag\Common\Tracker\SessionTracker;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;

final class EasyBugsnagBundleTest extends AbstractUnitTestCase
{
    public function testDefaultConfiguratorsOff(): void
    {
        self::bootKernel(['environment' => 'default_configurators_off']);

        self::assertFalse(self::getContainer()->has(BasicsClientConfigurator::class));
        self::assertFalse(self::getContainer()->has(RuntimeVersionClientConfigurator::class));
    }

    public function testSanityCheck(): void
    {
        self::assertInstanceOf(Client::class, self::getService(Client::class));
        self::assertInstanceOf(SessionTracker::class, self::getService(SessionTracker::class));
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony;

use EonX\EasySecurity\Bridge\EasyBugsnag\SecurityContextClientConfigurator;
use EonX\EasySecurity\Configurators\DefaultSecurityContextConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;

final class EasySecurityBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/config/default.yaml'])->getContainer();

        $container->get(SecurityContextResolverInterface::class)->setConfigurator(new DefaultSecurityContextConfigurator());

        self::assertInstanceOf(SecurityContextInterface::class, $container->get('service-id'));
        self::assertTrue($container->has(SecurityContextClientConfigurator::class));
    }
}

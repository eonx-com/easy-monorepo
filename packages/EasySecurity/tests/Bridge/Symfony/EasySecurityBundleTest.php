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
        $container = $this->getKernel([])->getContainer();

        /** @var \EonX\EasySecurity\Interfaces\SecurityContextResolverInterface $result */
        $result = $container->get(SecurityContextResolverInterface::class)
            ->setConfigurator(new DefaultSecurityContextConfigurator());

        self::assertInstanceOf(SecurityContextInterface::class, $result->resolveContext());
        self::assertTrue($container->has(SecurityContextClientConfigurator::class));
    }
}

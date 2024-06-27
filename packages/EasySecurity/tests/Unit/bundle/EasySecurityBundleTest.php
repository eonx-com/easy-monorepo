<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Bundle;

use EonX\EasySecurity\Common\Configurator\DefaultSecurityContextConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasySecurity\EasyBugsnag\Configurator\SecurityContextClientConfigurator;
use EonX\EasySecurity\Tests\Unit\AbstractSymfonyTestCase;

final class EasySecurityBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([])->getContainer();

        /** @var \EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface $result */
        $result = $container->get(SecurityContextResolverInterface::class)
            ->setConfigurator(new DefaultSecurityContextConfigurator());

        self::assertInstanceOf(SecurityContextInterface::class, $result->resolveContext());
        self::assertTrue($container->has(SecurityContextClientConfigurator::class));
    }
}

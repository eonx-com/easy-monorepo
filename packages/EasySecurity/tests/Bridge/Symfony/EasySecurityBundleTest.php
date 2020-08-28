<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

final class EasySecurityBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/config/default.yaml'])->getContainer();

        self::assertInstanceOf(SecurityContextInterface::class, $container->get('service-id'));
    }
}

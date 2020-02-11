<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Helpers;

use EonX\EasySecurity\Bridge\Symfony\Helpers\DeferredContextResolver;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasySecurity\Tests\Stubs\ContextFactoryInterfaceStub;

final class DeferredContextResolverTest extends AbstractSymfonyTestCase
{
    /**
     * Helper should return expected context instance from container.
     *
     * @return void
     */
    public function testResolve(): void
    {
        $container = $this->getKernel()->getContainer();

        $helper = new DeferredContextResolver($container, 'service-id');

        self::assertInstanceOf(ContextInterface::class, $helper->resolve());
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Helpers;

use EonX\EasySecurity\Bridge\Symfony\Helpers\DeferredContextResolver;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class DeferredContextResolverTest extends AbstractSymfonyTestCase
{
    /**
     * Helper should return expected context instance from container.
     *
     * @return void
     */
    public function testResolve(): void
    {
        $context = new Context();
        $container = $this->getKernel()->getContainer();

        $container->set('service-id', $context);

        $helper = new DeferredContextResolver($container, 'service-id');

        self::assertSame($context, $helper->resolve());
    }
}

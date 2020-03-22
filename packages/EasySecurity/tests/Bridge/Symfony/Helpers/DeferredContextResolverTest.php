<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Helpers;

use EonX\EasySecurity\Bridge\Symfony\Helpers\DeferredContextResolver;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class DeferredContextResolverTest extends AbstractSymfonyTestCase
{
    public function testResolve(): void
    {
        $container = $this->getKernel()->getContainer();

        $helper = new DeferredContextResolver($container, 'service-id');

        self::assertInstanceOf(ContextInterface::class, $helper->resolve());
    }
}

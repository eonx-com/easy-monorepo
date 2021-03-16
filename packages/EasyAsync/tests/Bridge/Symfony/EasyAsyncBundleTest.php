<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Symfony;

use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;

final class EasyAsyncBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertInstanceOf(BatchFactoryInterface::class, $container->get(BatchFactoryInterface::class));
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;

final class EasyBatchSymfonyBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertInstanceOf(BatchObjectManagerInterface::class, $container->get(BatchObjectManagerInterface::class));
    }
}

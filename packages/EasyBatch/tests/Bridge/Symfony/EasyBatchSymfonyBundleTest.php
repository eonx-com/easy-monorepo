<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use EonX\EasyBatch\Interfaces\BatchFactoryInterface;

final class EasyBatchSymfonyBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel()->getContainer();

        $batchFactory = $container->get(BatchFactoryInterface::class);

        self::assertInstanceOf(BatchFactoryInterface::class, $batchFactory);
    }
}

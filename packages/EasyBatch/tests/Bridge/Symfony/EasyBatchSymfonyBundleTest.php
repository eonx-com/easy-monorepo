<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

final class EasyBatchSymfonyBundleTest extends AbstractSymfonyTestCase
{
    /**
     * Make sure we can boot the kernel correctly.
     */
    public function testSanity(): void
    {
        $this->getKernel()
            ->getContainer();

        self::assertTrue(true);
    }
}

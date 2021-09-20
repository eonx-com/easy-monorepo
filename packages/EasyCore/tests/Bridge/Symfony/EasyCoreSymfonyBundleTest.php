<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony;

final class EasyCoreSymfonyBundleTest extends AbstractSymfonyTestCase
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

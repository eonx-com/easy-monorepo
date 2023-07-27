<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Bridge\Symfony;

final class EasyDoctrineSymfonyBundleTest extends AbstractSymfonyTestCase
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

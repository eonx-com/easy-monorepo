<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class EasyRandomBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $randomGenerator = $this->getKernel()->getContainer()->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGeneratorInterface::class, $randomGenerator);
    }
}

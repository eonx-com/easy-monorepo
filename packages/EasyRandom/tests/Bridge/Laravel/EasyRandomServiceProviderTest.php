<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Laravel;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class EasyRandomServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $randomGenerator = $this->getApp()->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGeneratorInterface::class, $randomGenerator);
    }
}

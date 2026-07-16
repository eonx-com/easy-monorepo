<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\ValueObject;

use EonX\EasyRandom\Exception\InvalidAlphabetException;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyRandom\ValueObject\RandomStringConfig;

final class RandomStringConfigTest extends AbstractUnitTestCase
{
    public function testInvalidAlphabetExceptionThrown(): void
    {
        $this->expectException(InvalidAlphabetException::class);
        $sut = new RandomStringConfig(8)
            ->alphabet('');

        $sut->resolveAlphabet();
    }
}

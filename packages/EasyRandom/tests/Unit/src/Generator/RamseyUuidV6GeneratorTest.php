<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Generator\RamseyUuidV6Generator;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV6 as SymfonyUuidV6;

final class RamseyUuidV6GeneratorTest extends AbstractUnitTestCase
{
    public function testGenerateSucceeds(): void
    {
        $sut = new RamseyUuidV6Generator();

        $result = $sut->generate();

        self::assertTrue(RamseyUuid::isValid($result));
        self::assertTrue(SymfonyUuidV6::isValid($result));
    }
}

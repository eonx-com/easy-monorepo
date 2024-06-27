<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Generator\SymfonyUuidV6Generator;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV6 as SymfonyUuidV6;

final class SymfonyUuidV6GeneratorTest extends AbstractUnitTestCase
{
    public function testGenerateSucceeds(): void
    {
        $sut = new SymfonyUuidV6Generator();

        $result = $sut->generate();

        self::assertTrue(RamseyUuid::isValid($result));
        self::assertTrue(SymfonyUuidV6::isValid($result));
    }
}

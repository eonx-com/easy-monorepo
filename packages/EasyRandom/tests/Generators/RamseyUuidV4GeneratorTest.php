<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Generators;

use EonX\EasyRandom\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Tests\AbstractTestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV4 as SymfonyUuidV4;

final class RamseyUuidV4GeneratorTest extends AbstractTestCase
{
    public function testGenerateSucceeds(): void
    {
        $sut = new RamseyUuidV4Generator();

        $result = $sut->generate();

        self::assertTrue(RamseyUuid::isValid($result));
        self::assertTrue(SymfonyUuidV4::isValid($result));
    }
}

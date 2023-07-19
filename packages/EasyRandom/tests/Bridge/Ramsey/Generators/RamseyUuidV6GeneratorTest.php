<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Ramsey\Generators;

use EonX\EasyRandom\Bridge\Ramsey\Generators\RamseyUuidV6Generator;
use EonX\EasyRandom\Tests\AbstractTestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV6 as SymfonyUuidV6;

final class RamseyUuidV6GeneratorTest extends AbstractTestCase
{
    public function testGenerateSucceeds(): void
    {
        $sut = new RamseyUuidV6Generator();

        $result = $sut->generate();

        self::assertTrue(RamseyUuid::isValid($result));
        self::assertTrue(SymfonyUuidV6::isValid($result));
    }
}

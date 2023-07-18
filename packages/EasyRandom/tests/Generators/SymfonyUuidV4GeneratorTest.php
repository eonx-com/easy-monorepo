<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Generators;

use EonX\EasyRandom\Generators\SymfonyUuidV4Generator;
use EonX\EasyRandom\Tests\AbstractTestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV4 as SymfonyUuidV4;

final class SymfonyUuidV4GeneratorTest extends AbstractTestCase
{
    public function testGenerateSucceeds(): void
    {
        $sut = new SymfonyUuidV4Generator();

        $result = $sut->generate();

        self::assertTrue(RamseyUuid::isValid($result));
        self::assertTrue(SymfonyUuidV4::isValid($result));
    }
}

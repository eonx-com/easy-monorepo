<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony\Generators;

use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Tests\AbstractTestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV6 as SymfonyUuidV6;

final class SymfonyUuidV6GeneratorTest extends AbstractTestCase
{
    public function testGenerateSucceeds(): void
    {
        $sut = new SymfonyUuidV6Generator();

        $result = $sut->generate();

        self::assertTrue(RamseyUuid::isValid($result));
        self::assertTrue(SymfonyUuidV6::isValid($result));
    }
}

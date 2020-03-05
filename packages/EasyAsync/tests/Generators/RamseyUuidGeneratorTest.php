<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Generators;

use EonX\EasyAsync\Generators\RamseyUuidGenerator;
use EonX\EasyAsync\Tests\AbstractTestCase;
use Ramsey\Uuid\Uuid;

final class RamseyUuidGeneratorTest extends AbstractTestCase
{
    public function testGenerate(): void
    {
        self::assertTrue(Uuid::isValid((new RamseyUuidGenerator())->generate()));
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Data;

use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class TargetTest extends AbstractTestCase
{
    /**
     * Target should return given values.
     *
     * @return void
     */
    public function testTarget(): void
    {
        $target = new Target('id', 'type');

        self::assertEquals('id', $target->getTargetId());
        self::assertEquals('type', $target->getTargetType());
    }
}

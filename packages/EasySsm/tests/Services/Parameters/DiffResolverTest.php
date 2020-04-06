<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Parameters;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Parameters\DiffResolver;
use EonX\EasySsm\Tests\AbstractTestCase;

final class DiffResolverTest extends AbstractTestCase
{
    public function testDiffIdentical(): void
    {
        $param1 = new SsmParameter('param1', 'string', 'value1');
        $param2 = new SsmParameter('param2', 'string', 'value2');

        $params = [$param1, $param2];

        $diff = (new DiffResolver(new Parameters()))->diff($params, $params);

        self::assertFalse($diff->isDifferent());
        self::assertEmpty($diff->getNew());
        self::assertEmpty($diff->getUpdated());
        self::assertEmpty($diff->getDeleted());
    }

    public function testDiffIsDifferent(): void
    {
        $param1 = new SsmParameter('param1', 'string', 'value1');
        $param2 = new SsmParameter('param2', 'string', 'value2');
        $param1Updated = new SsmParameter('param1', 'string', 'value1Updated');
        $param3 = new SsmParameter('param3', 'string', 'value3');

        $local = [$param1Updated, $param2];
        $remote = [$param1, $param3];

        $diff = (new DiffResolver(new Parameters()))->diff($remote, $local);

        self::assertTrue($diff->isDifferent());
        self::assertCount(1, $diff->getNew());
        self::assertCount(1, $diff->getUpdated());
        self::assertCount(1, $diff->getDeleted());
        self::assertSame($param2, $diff->getNew()[0]);
        self::assertSame($param1Updated, $diff->getUpdated()[0]);
        self::assertSame($param3, $diff->getDeleted()[0]);
    }
}

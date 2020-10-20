<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge;

use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stubs\WithProcessJobLogDataStub;

/**
 * @coversNothing
 */
final class WithProcessJobLogDataTraitTest extends AbstractTestCase
{
    public function testTrait(): void
    {
        $stub = new WithProcessJobLogDataStub();
        $stub->setTarget(new Target('id', 'type'));
        $stub->setJobId('jobId');
        $stub->setType('test');

        $data = $stub->getProcessJobLogData();

        self::assertEquals('id', $data->getTarget()->getTargetId());
        self::assertEquals('type', $data->getTarget()->getTargetType());
        self::assertEquals('jobId', $data->getJobId());
        self::assertEquals('test', $data->getType());
    }
}

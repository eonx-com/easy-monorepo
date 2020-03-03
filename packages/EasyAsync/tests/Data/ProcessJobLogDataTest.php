<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Data;

use EonX\EasyAsync\Data\ProcessJobLogData;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class ProcessJobLogDataTest extends AbstractTestCase
{
    /**
     * Test process job log data setters and getters.
     *
     * @return void
     */
    public function testProcessJobLogData(): void
    {
        $target = new Target('id', 'type');
        $data = new ProcessJobLogData('jobId', $target, 'test');

        self::assertEquals('jobId', $data->getJobId());
        self::assertEquals('test', $data->getType());
        self::assertEquals(\spl_object_hash($target), \spl_object_hash($data->getTarget()));
    }
}

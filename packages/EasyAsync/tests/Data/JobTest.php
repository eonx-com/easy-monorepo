<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Data;

use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class JobTest extends AbstractTestCase
{
    public function testJob(): void
    {
        $job = new Job(new Target('id', 'type'), 'test');

        $expected = [
            'failed' => 2,
            'processed' => 2,
            'succeeded' => 2,
            'total' => 1,
            'target_type' => 'type',
            'target_id' => 'id',
            'finished_at' => null,
            'id' => null,
            'started_at' => null,
            'status' => 'scheduled',
            'type' => 'test',
        ];

        self::assertEquals(0, $job->getFailed());
        self::assertEquals(0, $job->getProcessed());
        self::assertEquals(0, $job->getSucceeded());
        self::assertEquals(1, $job->getTotal());
        self::assertEquals('test', $job->getType());
        self::assertEquals('id', $job->getTargetId());
        self::assertEquals('type', $job->getTargetType());

        $job
            ->setFailed(2)
            ->setProcessed(2)
            ->setSucceeded(2);

        self::assertEquals(2, $job->getFailed());
        self::assertEquals(2, $job->getProcessed());
        self::assertEquals(2, $job->getSucceeded());
        self::assertEquals($expected, $job->toArray());
    }

    public function testJobFromArray(): void
    {
        $expected = [
            'failed' => 2,
            'processed' => 2,
            'succeeded' => 2,
            'total' => 1,
            'target_type' => 'type',
            'target_id' => 'id',
            'finished_at' => null,
            'id' => 'jobId',
            'started_at' => null,
            'status' => 'scheduled',
            'type' => 'test',
        ];

        $job = Job::fromArray($expected);

        self::assertEquals($expected['failed'], $job->getFailed());
        self::assertEquals($expected['processed'], $job->getProcessed());
        self::assertEquals($expected['succeeded'], $job->getSucceeded());
        self::assertEquals($expected['total'], $job->getTotal());
        self::assertEquals($expected['type'], $job->getType());
        self::assertEquals($expected['target_id'], $job->getTargetId());
        self::assertEquals($expected['target_type'], $job->getTargetType());
        self::assertEquals($expected['id'], $job->getId());
    }
}

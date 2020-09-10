<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Factories;

use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Factories\JobLogFactory;
use EonX\EasyAsync\Interfaces\TargetInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class JobLogFactoryTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerCreate(): iterable
    {
        yield 'Create job log' => [new Target('id', 'type'), 'test', 'jobId'];
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate(TargetInterface $target, string $type, string $jobId): void
    {
        $jobLog = (new JobLogFactory())->create($target, $type, $jobId);

        self::assertEquals($target->getTargetId(), $jobLog->getTargetId());
        self::assertEquals($target->getTargetType(), $jobLog->getTargetType());
        self::assertEquals($type, $jobLog->getType());
        self::assertEquals($jobId, $jobLog->getJobId());
    }
}

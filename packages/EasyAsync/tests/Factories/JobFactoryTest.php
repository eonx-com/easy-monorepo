<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Factories;

use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Factories\JobFactory;
use EonX\EasyAsync\Interfaces\TargetInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
final class JobFactoryTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testCreate
     */
    public function providerCreate(): iterable
    {
        yield 'Create job without total' => [new Target('id', 'type'), 'test'];

        yield 'Create job with total' => [new Target('id', 'type'), 'test', 100];
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate(TargetInterface $target, string $type, ?int $total = null): void
    {
        $job = (new JobFactory())->create($target, $type, $total);

        self::assertEquals($target->getTargetId(), $job->getTargetId());
        self::assertEquals($target->getTargetType(), $job->getTargetType());
        self::assertEquals($type, $job->getType());
        self::assertEquals($total ?? 1, $job->getTotal());
    }
}

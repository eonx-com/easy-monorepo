<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use EonX\EasyUtils\Exceptions\InvalidArgumentException;
use EonX\EasyUtils\Helpers\CollectorHelper;
use EonX\EasyUtils\Tests\Stubs\HasPriorityStub;

final class CollectorHelperTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestEnsureClass(): iterable
    {
        yield 'basic type' => [[0], true];

        yield 'basic object' => [[new \stdClass()], false];

        yield 'interface based' => [[new HasPriorityStub(), new \stdClass()], true];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testFilterByClass
     */
    public function providerTestFilterByClass(): iterable
    {
        yield 'basic types' => [[0, 'string', 12.00, []], 0];

        yield 'basic object' => [[new \stdClass()], 1];

        yield 'interface based' => [[new HasPriorityStub(), new \stdClass()], 1];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testOrderHigherPriorityFirst
     */
    public function providerTestOrderHigherPriorityFirst(): iterable
    {
        $priority1 = $this->hasPriorityStub(1);
        $priority10 = $this->hasPriorityStub(10);

        yield 'same order' => [[$priority10, $priority1], [$priority10, $priority1]];

        yield 'order changed' => [[$priority1, $priority10], [$priority10, $priority1]];

        $noPriority1 = new \stdClass();
        $noPriority2 = new \stdClass();

        yield 'same order when no priority' => [[$noPriority1, $noPriority2], [$noPriority1, $noPriority2]];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testOrderLowerPriorityFirst
     */
    public function providerTestOrderLowerPriorityFirst(): iterable
    {
        $priority1 = $this->hasPriorityStub(1);
        $priority10 = $this->hasPriorityStub(10);

        yield 'same order' => [[$priority1, $priority10], [$priority1, $priority10]];

        yield 'order changed' => [[$priority10, $priority1], [$priority1, $priority10]];

        $noPriority1 = new \stdClass();
        $noPriority2 = new \stdClass();

        yield 'same order when no priority' => [[$noPriority1, $noPriority2], [$noPriority1, $noPriority2]];
    }

    /**
     * @param iterable<mixed> $items
     *
     * @dataProvider providerTestEnsureClass
     */
    public function testEnsureClass(iterable $items, bool $expectException, ?string $class = null): void
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        // Convert to array so it goes through the generator
        CollectorHelper::ensureClassAsArray($items, $class ?? \stdClass::class);

        // If it reaches here, test is valid
        self::assertTrue(true);
    }

    /**
     * @param iterable<mixed> $items
     * @param null|class-string $class
     *
     * @dataProvider providerTestFilterByClass
     */
    public function testFilterByClass(iterable $items, int $expectedCount, ?string $class = null): void
    {
        $class = $class ?? \stdClass::class;
        $results = CollectorHelper::filterByClassAsArray($items, $class);

        self::assertCount($expectedCount, $results);

        foreach ($results as $result) {
            self::assertInstanceOf($class, $result);
        }
    }

    /**
     * @param iterable<mixed> $items
     * @param mixed[] $expected
     *
     * @dataProvider providerTestOrderHigherPriorityFirst
     */
    public function testOrderHigherPriorityFirst(iterable $items, array $expected): void
    {
        self::assertEquals($expected, CollectorHelper::orderHigherPriorityFirstAsArray($items));
    }

    /**
     * @param iterable<mixed> $items
     * @param mixed[] $expected
     *
     * @dataProvider providerTestOrderLowerPriorityFirst
     */
    public function testOrderLowerPriorityFirst(iterable $items, array $expected): void
    {
        self::assertEquals($expected, CollectorHelper::orderLowerPriorityFirstAsArray($items));
    }

    private function hasPriorityStub(?int $priority = null): HasPriorityStub
    {
        return new HasPriorityStub($priority);
    }
}

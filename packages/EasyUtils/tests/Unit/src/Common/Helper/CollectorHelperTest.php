<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Helper;

use EonX\EasyUtils\Common\Exception\InvalidArgumentException;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use EonX\EasyUtils\Tests\Stub\Helper\HasPriorityHelperStub;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class CollectorHelperTest extends AbstractUnitTestCase
{
    /**
     * @see testEnsureClass
     */
    public static function provideEnsureClassData(): iterable
    {
        yield 'basic type' => [[0], true];

        yield 'basic object' => [[new stdClass()], false];

        yield 'interface based' => [[new HasPriorityHelperStub(), new stdClass()], true];
    }

    /**
     * @see testFilterByClass
     */
    public static function provideFilterByClassData(): iterable
    {
        yield 'basic types' => [[0, 'string', 12.00, []], 0];

        yield 'basic object' => [[new stdClass()], 1];

        yield 'interface based' => [[new HasPriorityHelperStub(), new stdClass()], 1];
    }

    /**
     * @see testOrderHigherPriorityFirst
     */
    public static function provideOrderHigherPriorityFirstData(): iterable
    {
        $priority1 = self::hasPriorityStub(1);
        $priority10 = self::hasPriorityStub(10);

        yield 'same order' => [[$priority10, $priority1], [$priority10, $priority1]];

        yield 'order changed' => [[$priority1, $priority10], [$priority10, $priority1]];

        $noPriority1 = new stdClass();
        $noPriority2 = new stdClass();

        yield 'same order when no priority' => [[$noPriority1, $noPriority2], [$noPriority1, $noPriority2]];
    }

    /**
     * @see testOrderLowerPriorityFirst
     */
    public static function provideOrderLowerPriorityFirstData(): iterable
    {
        $priority1 = self::hasPriorityStub(1);
        $priority10 = self::hasPriorityStub(10);

        yield 'same order' => [[$priority1, $priority10], [$priority1, $priority10]];

        yield 'order changed' => [[$priority10, $priority1], [$priority1, $priority10]];

        $noPriority1 = new stdClass();
        $noPriority2 = new stdClass();

        yield 'same order when no priority' => [[$noPriority1, $noPriority2], [$noPriority1, $noPriority2]];
    }

    #[DataProvider('provideEnsureClassData')]
    public function testEnsureClass(iterable $items, bool $expectException, ?string $class = null): void
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        // Convert to array so it goes through the generator
        CollectorHelper::ensureClassAsArray($items, $class ?? stdClass::class);

        // If it reaches here, test is valid
        self::assertTrue(true);
    }

    /**
     * @param class-string|null $class
     */
    #[DataProvider('provideFilterByClassData')]
    public function testFilterByClass(iterable $items, int $expectedCount, ?string $class = null): void
    {
        $class ??= stdClass::class;
        $results = CollectorHelper::filterByClassAsArray($items, $class);

        self::assertCount($expectedCount, $results);

        foreach ($results as $result) {
            self::assertInstanceOf($class, $result);
        }
    }

    #[DataProvider('provideOrderHigherPriorityFirstData')]
    public function testOrderHigherPriorityFirst(iterable $items, array $expected): void
    {
        self::assertEquals($expected, CollectorHelper::orderHigherPriorityFirstAsArray($items));
    }

    #[DataProvider('provideOrderLowerPriorityFirstData')]
    public function testOrderLowerPriorityFirst(iterable $items, array $expected): void
    {
        self::assertEquals($expected, CollectorHelper::orderLowerPriorityFirstAsArray($items));
    }

    private static function hasPriorityStub(?int $priority = null): HasPriorityHelperStub
    {
        return new HasPriorityHelperStub($priority);
    }
}

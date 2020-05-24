<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\StrictInArrayRector;

use EonX\EasyStandard\Rector\StrictInArrayRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \EonX\EasyStandard\Rector\StrictInArrayRector
 *
 * @internal
 */
final class StrictInArrayRectorTest extends AbstractRectorTestCase
{
    /**
     * Provides test examples.
     *
     * @return Iterator<array>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * Tests Rector rule.
     *
     * @dataProvider provideData()
     */
    public function testRule(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * Returns Rector with configuration.
     *
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            StrictInArrayRector::class => [],
        ];
    }
}

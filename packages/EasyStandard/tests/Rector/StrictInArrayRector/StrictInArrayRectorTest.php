<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\StrictInArrayRector;

use EonX\EasyStandard\Rector\StrictInArrayRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Iterator;

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
     * @return \Iterator
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * Tests Rector rule.
     *
     * @param string $file
     *
     * @return void
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

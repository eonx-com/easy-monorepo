<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\RestoreDefaultNullToNullableTypeParameterRector;

use EonX\EasyStandard\Rector\RestoreDefaultNullToNullableTypeParameterRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \EonX\EasyStandard\Rector\RestoreDefaultNullToNullableTypeParameterRector
 *
 * @internal
 */
final class RestoreDefaultNullToNullableTypeParameterRectorTest extends AbstractRectorTestCase
{
    /**
     * Provides test examples.
     *
     * @return \Iterator<array>
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
            RestoreDefaultNullToNullableTypeParameterRector::class => [],
        ];
    }
}

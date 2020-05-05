<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AddCoversAnnotationRector;

use EonX\EasyStandard\Rector\AddCoversAnnotationRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \EonX\EasyStandard\Rector\AddCoversAnnotationRector
 *
 * @internal
 */
final class AddCoversAnnotationRectorTest extends AbstractRectorTestCase
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
            AddCoversAnnotationRector::class => [
                '$replaceArr' => [
                    'Tests\\Unit\\',
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AddSeeAnnotationRector;

use EonX\EasyStandard\Rector\AddSeeAnnotationRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\AddSeeAnnotationRector
 *
 * @internal
 */
final class AddSeeAnnotationRectorTest extends AbstractRectorTestCase
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
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * Returns Rector with configuration.
     *
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddSeeAnnotationRector::class => [],
        ];
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector;

use EonX\EasyStandard\Rector\AnnotationsCommentsRector;
use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\AnnotationsCommentsRector
 *
 * @internal
 */
final class AnnotationsCommentsRectorTest extends AbstractRectorTestCase
{
    /**
     * @return Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
     *
     * @see testRule
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @dataProvider provideData
     */
    public function testRule(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    protected function getRectorClass(): string
    {
        return AnnotationsCommentsRector::class;
    }
}

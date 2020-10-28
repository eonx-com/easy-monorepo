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
     * @return Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @dataProvider provideData()
     */
    public function testRule(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    protected function getRectorClass(): string
    {
        return AddSeeAnnotationRector::class;
    }
}

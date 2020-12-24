<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\ExplicitBoolCompareRector;

use EonX\EasyStandard\Rector\ExplicitBoolCompareRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

;

/**
 * @covers \EonX\EasyStandard\Rector\StrictInArrayRector
 *
 * @internal
 */
final class ExplicitBoolCompareRectorTest extends AbstractRectorTestCase
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
        return ExplicitBoolCompareRector::class;
    }
}

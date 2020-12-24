<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\RestoreDefaultNullToNullableTypeParameterRector;

use EonX\EasyStandard\Rector\RestoreDefaultNullToNullableTypeParameterRector;
use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\RestoreDefaultNullToNullableTypeParameterRector
 *
 * @internal
 */
final class RestoreDefaultNullToNullableTypeParameterRectorTest extends AbstractRectorTestCase
{
    /**
     * @return \Iterator<SmartFileInfo>
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
        return RestoreDefaultNullToNullableTypeParameterRector::class;
    }
}

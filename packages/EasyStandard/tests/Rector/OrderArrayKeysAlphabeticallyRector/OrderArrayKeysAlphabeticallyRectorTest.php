<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\OrderArrayKeysAlphabeticallyRector;

use EonX\EasyStandard\Rector\OrderArrayKeysAlphabeticallyRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\OrderArrayKeysAlphabeticallyRector
 *
 * @internal
 */
final class OrderArrayKeysAlphabeticallyRectorTest extends AbstractRectorTestCase
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
        return OrderArrayKeysAlphabeticallyRector::class;
    }
}

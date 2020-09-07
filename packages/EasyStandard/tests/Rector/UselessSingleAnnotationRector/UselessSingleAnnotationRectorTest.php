<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\UselessSingleAnnotationRector;

use EonX\EasyStandard\Rector\UselessSingleAnnotationRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\UselessSingleAnnotationRector
 *
 * @internal
 */
final class UselessSingleAnnotationRectorTest extends AbstractRectorTestCase
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

    /**
     * Returns Rector with configuration.
     *
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            UselessSingleAnnotationRector::class => [
                UselessSingleAnnotationRector::ANNOTATIONS => [
                    '{@inheritDoc}',
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\ExplicitBoolCompareRector;

use EonX\EasyStandard\Rector\ExplicitBoolCompareRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\StrictInArrayRector
 *
 * @internal
 */
final class ExplicitBoolCompareRectorTest extends AbstractRectorTestCase
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
    public function testRule(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ExplicitBoolCompareRector::class => [],
        ];
    }
}

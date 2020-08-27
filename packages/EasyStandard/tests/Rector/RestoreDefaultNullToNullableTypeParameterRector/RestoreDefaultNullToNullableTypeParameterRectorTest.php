<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\RestoreDefaultNullToNullableTypeParameterRector;

use EonX\EasyStandard\Rector\RestoreDefaultNullToNullableTypeParameterRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

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
            RestoreDefaultNullToNullableTypeParameterRector::class => [],
        ];
    }
}

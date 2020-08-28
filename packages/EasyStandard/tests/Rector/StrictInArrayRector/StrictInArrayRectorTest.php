<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\StrictInArrayRector;

use EonX\EasyStandard\Rector\StrictInArrayRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\StrictInArrayRector
 *
 * @internal
 */
final class StrictInArrayRectorTest extends AbstractRectorTestCase
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

    public function testGetDefinitionSucceeds(): void
    {
        $rector = new StrictInArrayRector();

        $definition = $rector->getDefinition();

        self::assertNotEmpty($definition->getDescription());
    }

    /**
     * Returns Rector with configuration.
     *
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            StrictInArrayRector::class => [],
        ];
    }
}

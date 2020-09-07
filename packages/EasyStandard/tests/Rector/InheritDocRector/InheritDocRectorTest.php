<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\InheritDocRector;

use EonX\EasyStandard\Rector\InheritDocRector;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Rector\InheritDocRector
 *
 * @internal
 */
final class InheritDocRectorTest extends AbstractRectorTestCase
{
    /**
     * @return Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function testGetDefinitionSucceeds(): void
    {
        $rector = new InheritDocRector();

        $definition = $rector->getDefinition();

        self::assertNotEmpty($definition->getDescription());
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
        return InheritDocRector::class;
    }
}

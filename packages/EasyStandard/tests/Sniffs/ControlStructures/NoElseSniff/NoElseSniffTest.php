<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures\NoElseSniff;

use EonX\EasyStandard\Sniffs\ControlStructures\NoElseSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NoElseSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<array<int, SmartFileInfo|int>>
     */
    public function providerTestSniff(): iterable
    {
        yield [new SmartFileInfo(__DIR__ . '/../NoElseSniff/Fixture/NoElseSniffTest.php.inc'), 1];
    }

    /**
     * @dataProvider providerTestSniff()
     */
    public function testSniff(SmartFileInfo $smartFileInfo, int $expectedErrorCount): void
    {
        $this->doTestFileInfoWithErrorCountOf($smartFileInfo, $expectedErrorCount);
    }

    protected function getCheckerClass(): string
    {
        return NoElseSniff::class;
    }
}

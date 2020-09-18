<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Classes\StrictDeclarationFormatSniff;

use EonX\EasyStandard\Sniffs\Classes\StrictDeclarationFormatSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StrictDeclarationFormatSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<array<int, SmartFileInfo|int>>
     */
    public function providerTestSniff(): iterable
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/StrictDeclarationFormatSniffTest_ExtraLine.php.inc'), 1];
        yield [new SmartFileInfo(__DIR__ . '/Fixture/StrictDeclarationFormatSniffTest_SameLine.php.inc'), 2];
    }

    /**
     * @dataProvider providerTestSniff
     */
    public function testSniff(SmartFileInfo $smartFileInfo, int $expectedErrorCount): void
    {
        $this->doTestFileInfoWithErrorCountOf($smartFileInfo, $expectedErrorCount);
    }

    protected function getCheckerClass(): string
    {
        return StrictDeclarationFormatSniff::class;
    }
}

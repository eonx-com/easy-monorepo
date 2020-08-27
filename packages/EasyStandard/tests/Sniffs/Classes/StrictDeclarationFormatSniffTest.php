<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Classes;

use EonX\EasyStandard\Sniffs\Classes\StrictDeclarationFormatSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StrictDeclarationFormatSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSniff(): iterable
    {
        yield [__DIR__ . '/../../fixtures/Sniffs/Classes/StrictDeclarationFormatSniffTest_ExtraLine.php.inc', 1];
        yield [__DIR__ . '/../../fixtures/Sniffs/Classes/StrictDeclarationFormatSniffTest_SameLine.php.inc', 2];
    }

    /**
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     *
     * @dataProvider providerTestSniff
     */
    public function testSniff(string $file, int $errorCount): void
    {
        $this->doTestFileInfoWithErrorCountOf(new SmartFileInfo($file), $errorCount);
    }

    protected function getCheckerClass(): string
    {
        return StrictDeclarationFormatSniff::class;
    }
}

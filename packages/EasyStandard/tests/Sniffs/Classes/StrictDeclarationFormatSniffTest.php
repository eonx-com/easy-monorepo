<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Classes;

use EonX\EasyStandard\Sniffs\Classes\StrictDeclarationFormatSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

final class StrictDeclarationFormatSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSniff(): iterable
    {
        yield [__DIR__ . '/../../fixtures/Sniffs/Classes/StrictDeclarationFormatSniffTest_ExtraLine.php.inc'];
        yield [__DIR__ . '/../../fixtures/Sniffs/Classes/StrictDeclarationFormatSniffTest_SameLine.php.inc'];
    }

    /**
     * @dataProvider providerTestSniff
     */
    public function testSniff(string $file): void
    {
        $this->doTestWrongFile($file);
    }

    protected function getCheckerClass(): string
    {
        return StrictDeclarationFormatSniff::class;
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Classes;

use EonX\EasyStandard\Sniffs\Classes\RequireStrictDeclarationSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RequireStrictDeclarationSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSniff(): iterable
    {
        yield [__DIR__ . '/../../fixtures/Sniffs/Classes/RequireStrictDeclarationSniffTest.php.inc'];
    }

    /**
     * @dataProvider providerTestSniff
     */
    public function testSniff(string $file): void
    {
        $this->doTestFileInfo(new SmartFileInfo($file));
    }

    protected function getCheckerClass(): string
    {
        return RequireStrictDeclarationSniff::class;
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Classes\RequirePublicConstructorSniff;

use EonX\EasyStandard\Sniffs\Classes\RequirePublicConstructorSniff;
use Iterator;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RequirePublicConstructorSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return Iterator<int, SmartFileInfo|int>
     */
    public function providerTestSniff(): Iterator
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/RequirePublicConstructorSniffTest.php.inc'), 1];
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
        return RequirePublicConstructorSniff::class;
    }
}

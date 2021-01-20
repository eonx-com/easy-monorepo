<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Classes\AvoidPrivatePropertiesSniff;

use EonX\EasyStandard\Sniffs\Classes\AvoidPrivatePropertiesSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AvoidPrivatePropertiesSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<array<int, SmartFileInfo|int>>
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function providerTestSniff(): iterable
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/AvoidPrivatePropertiesSniffTest.php.inc'), 1];
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
        return AvoidPrivatePropertiesSniff::class;
    }
}

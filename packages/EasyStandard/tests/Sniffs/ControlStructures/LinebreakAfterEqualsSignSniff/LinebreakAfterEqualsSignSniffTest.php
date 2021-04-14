<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures\LinebreakAfterEqualsSignSniff;

use EonX\EasyStandard\Sniffs\ControlStructures\LinebreakAfterEqualsSignSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\ControlStructures\LinebreakAfterEqualsSignSniff
 *
 * @internal
 */
final class LinebreakAfterEqualsSignSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return mixed[]
     *
     * @see testSniff
     */
    public function providerTestSniff(): iterable
    {
        yield [
            'filePath' => '/Fixture/LinebreakAfterEqualsSignSniffTest.php.inc',
        ];
    }

    /**
     * @param string $filePath
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     *
     * @dataProvider providerTestSniff
     */
    public function testSniff(string $filePath): void
    {
        $smartFileInfo = new SmartFileInfo(__DIR__ . $filePath);
        $this->doTestFileInfo($smartFileInfo);
    }

    protected function getCheckerClass(): string
    {
        return LinebreakAfterEqualsSignSniff::class;
    }
}

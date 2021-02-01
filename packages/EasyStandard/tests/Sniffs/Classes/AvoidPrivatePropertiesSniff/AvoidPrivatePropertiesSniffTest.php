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
     *
     * @see testSniffSucceeds
     */
    public function provideCorrectFiles(): iterable
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/ProtectedProperties.php.inc')];
        yield [new SmartFileInfo(__DIR__ . '/Fixture/PublicProperties.php.inc')];
    }

    /**
     * @return iterable<array<int, SmartFileInfo|int>>
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     *
     * @see testSniffFails
     */
    public function provideWrongFiles(): iterable
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/PrivateProperties.php.inc'), 1];
        yield [new SmartFileInfo(__DIR__ . '/Fixture/MissedScopeProperties.php.inc'), 1];
    }

    /**
     * @dataProvider provideWrongFiles()
     */
    public function testSniffFails(SmartFileInfo $smartFileInfo, int $expectedErrorCount): void
    {
        $this->doTestFileInfoWithErrorCountOf($smartFileInfo, $expectedErrorCount);
    }

    /**
     * @dataProvider provideCorrectFiles()
     */
    public function testSniffSucceeds(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestCorrectFileInfo($smartFileInfo);
    }

    protected function getCheckerClass(): string
    {
        return AvoidPrivatePropertiesSniff::class;
    }
}

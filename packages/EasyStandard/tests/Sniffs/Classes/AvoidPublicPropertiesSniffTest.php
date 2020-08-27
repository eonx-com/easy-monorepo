<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Classes;

use EonX\EasyStandard\Sniffs\Classes\AvoidPublicPropertiesSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AvoidPublicPropertiesSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSniff(): iterable
    {
        yield [__DIR__ . '/../../fixtures/Sniffs/Classes/AvoidPublicPropertiesSniffTest.php.inc'];
    }

    /**
     * @dataProvider providerTestSniff
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testSniff(string $file): void
    {
        $this->doTestFileInfoWithErrorCountOf(new SmartFileInfo($file), 1);
    }

    protected function getCheckerClass(): string
    {
        return AvoidPublicPropertiesSniff::class;
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Namespaces;

use EonX\EasyStandard\Sniffs\Namespaces\Psr4Sniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Psr4SniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSniff(): iterable
    {
        yield [__DIR__ . '/../../fixtures/Sniffs/Namespaces/Psr4SniffTest.php.inc'];
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
        return Psr4Sniff::class;
    }
}

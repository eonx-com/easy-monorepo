<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Namespaces;

use EonX\EasyStandard\Sniffs\Namespaces\Psr4Sniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

final class Psr4SniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSniff(): iterable
    {
        yield [__DIR__ . '/../fixtures/Psr4SniffTest.php.inc'];
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
        return Psr4Sniff::class;
    }
}

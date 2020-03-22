<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures;

use EonX\EasyStandard\Sniffs\ControlStructures\NoElseSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

final class NoElseSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestSniff(): iterable
    {
        yield [__DIR__ . '/../../fixtures/Sniffs/ControlStructures/NoElseSniffTest.php.inc'];
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
        return NoElseSniff::class;
    }
}

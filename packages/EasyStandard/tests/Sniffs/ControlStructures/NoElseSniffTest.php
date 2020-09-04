<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures;

use EonX\EasyStandard\Sniffs\ControlStructures\NoElseSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @deprecated use smart PHPStan instead: https://github.com/symplify/coding-standard/blob/master/docs/phpstan_rules.md#object-calisthenics-rules
 */
final class NoElseSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return iterable<SmartFileInfo>
     */
    public function providerTestSniff(): iterable
    {
        yield [new SmartFileInfo(__DIR__ . '/../../fixtures/Sniffs/ControlStructures/NoElseSniffTest.php.inc'), 1];
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
        return NoElseSniff::class;
    }
}

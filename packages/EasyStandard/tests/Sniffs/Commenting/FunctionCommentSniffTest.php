<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Commenting;

use EonX\EasyStandard\Sniffs\Commenting\FunctionCommentSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\Commenting\FunctionCommentSniff
 */
final class FunctionCommentSniffTest extends AbstractCheckerTestCase
{
    /**
     * @var string
     */
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures/Sniffs/Commenting/FunctionCommentSniff';

    /**
     * @return iterable<SmartFileInfo>
     */
    public function provideCorrectFixtures(): iterable
    {
        yield [new SmartFileInfo(self::FIXTURES_DIR . '/Correct/correct.php.inc')];
    }

    /**
     * @return iterable<SmartFileInfo|int>
     */
    public function provideWrongFixtures(): iterable
    {
        yield [new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/missingDocComment.php.inc'), 1];
        yield [new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/incorrectCommentStyle.php.inc'), 1];
        yield [new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/blankLineAfterComment.php.inc'), 2];
        yield [new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/missingParamDocComment.php.inc'), 1];
    }

    /**
     * @dataProvider provideCorrectFixtures()
     */
    public function testCorrectSniffs(SmartFileInfo $fileInfo): void
    {
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * @dataProvider provideWrongFixtures()
     */
    public function testWrongSniffs(SmartFileInfo $wrongFileInfo, int $expectedErrorCount): void
    {
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, $expectedErrorCount);
    }

    protected function getCheckerClass(): string
    {
        return FunctionCommentSniff::class;
    }
}

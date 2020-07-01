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
     * @return iterable<mixed>
     */
    public function provideFixtures(): iterable
    {
        yield [self::FIXTURES_DIR . '/Correct/correct.php.inc'];
        yield [self::FIXTURES_DIR . '/Wrong/missingDocComment.php.inc'];
        yield [self::FIXTURES_DIR . '/Wrong/incorrectCommentStyle.php.inc'];
        yield [self::FIXTURES_DIR . '/Wrong/blankLineAfterComment.php.inc'];
        yield [self::FIXTURES_DIR . '/Wrong/missingParamDocComment.php.inc'];
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testSniffs(string $file): void
    {
        $this->doTestFileInfo(new SmartFileInfo($file));
    }

    protected function getCheckerClass(): string
    {
        return FunctionCommentSniff::class;
    }
}

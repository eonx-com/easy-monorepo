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
    public function provideCorrectFixtures(): iterable
    {
        yield [self::FIXTURES_DIR . '/Correct/correct.php.inc'];
    }

    /**
     * @return iterable<mixed>
     */
    public function provideWrongFixtures(): iterable
    {
        yield [self::FIXTURES_DIR . '/Wrong/missingDocComment.php.inc', 1];
        yield [self::FIXTURES_DIR . '/Wrong/incorrectCommentStyle.php.inc', 1];
        yield [self::FIXTURES_DIR . '/Wrong/blankLineAfterComment.php.inc', 2];
        yield [self::FIXTURES_DIR . '/Wrong/missingParamDocComment.php.inc', 1];
    }

    /**
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     *
     * @dataProvider provideCorrectFixtures
     */
    public function testCorrectSniffs(string $file): void
    {
        $this->doTestCorrectFileInfo(new SmartFileInfo($file));
    }

    /**
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     *
     * @dataProvider provideWrongFixtures
     */
    public function testWrongSniffs(string $file, int $countErrors): void
    {
        $this->doTestFileInfoWithErrorCountOf(new SmartFileInfo($file), $countErrors);
    }

    protected function getCheckerClass(): string
    {
        return FunctionCommentSniff::class;
    }
}

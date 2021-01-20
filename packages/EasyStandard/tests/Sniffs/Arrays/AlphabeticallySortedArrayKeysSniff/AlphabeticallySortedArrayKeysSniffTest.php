<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff;

use EonX\EasyStandard\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff
 *
 * @internal
 */
final class AlphabeticallySortedArrayKeysSniffTest extends AbstractCheckerTestCase
{
    /**
     * @return mixed[]
     *
     * @see testProcessSucceeds
     */
    public function provideCorrectData(): array
    {
        return [
            'multi line array' => [
                'filePath' => '/Fixtures/Correct/MultiLineArray.php.inc',
            ],
            'multi line mixed array' => [
                'filePath' => '/Fixtures/Correct/MultiLineMixedArray.php.inc',
            ],
            'multi line multi dimensional array' => [
                'filePath' => '/Fixtures/Correct/MultiLineMultiDimensionalArray.php.inc',
            ],
            'single line array' => [
                'filePath' => '/Fixtures/Correct/SingleLineArray.php.inc',
            ],
            'single line mixed array' => [
                'filePath' => '/Fixtures/Correct/SingleLineMixedArray.php.inc',
            ],
            'single line multi dimensional array' => [
                'filePath' => '/Fixtures/Correct/SingleLineMultiDimensionalArray.php.inc',
            ],
            'skip by class name' => [
                'filePath' => '/Fixtures/Correct/SkipByClassName.php.inc',
            ],
            'skip by function name' => [
                'filePath' => '/Fixtures/Correct/SkipByFunctionName.php.inc',
            ],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testProcessFails
     */
    public function provideWrongData(): array
    {
        return [
            'multi line array' => [
                'filePath' => '/Fixtures/Wrong/MultiLineArray.php.inc',
            ],
            'multi line mixed array' => [
                'filePath' => '/Fixtures/Wrong/MultiLineMixedArray.php.inc',
            ],
            'multi line multi dimensional array' => [
                'filePath' => '/Fixtures/Wrong/MultiLineMultiDimensionalArray.php.inc',
            ],
            'single line array' => [
                'filePath' => '/Fixtures/Wrong/SingleLineArray.php.inc',
            ],
            'single line mixed array' => [
                'filePath' => '/Fixtures/Wrong/SingleLineMixedArray.php.inc',
            ],
            'single line multi dimensional array' => [
                'filePath' => '/Fixtures/Wrong/SingleLineMultiDimensionalArray.php.inc',
            ],
        ];
    }

    /**
     * @param string $filePath
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     *
     * @dataProvider provideWrongData
     */
    public function testProcessFails(string $filePath): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . $filePath);
        $this->doTestFileInfo($wrongFileInfo);
    }

    /**
     * @param string $filePath
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     *
     * @dataProvider provideCorrectData
     */
    public function testProcessSucceeds(string $filePath): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . $filePath);
        $this->doTestCorrectFileInfo($fileInfo);
    }

    protected function getCheckerClass(): string
    {
        return AlphabeticallySortedArrayKeysSniff::class;
    }

    /**
     * @return mixed[]
     */
    protected function getCheckerConfiguration(): array
    {
        return [
            'skipPatterns' => [
                T_CLASS => ['/^SomeClass/'],
                T_FUNCTION => ['/^provide/'],
            ],
        ];
    }
}

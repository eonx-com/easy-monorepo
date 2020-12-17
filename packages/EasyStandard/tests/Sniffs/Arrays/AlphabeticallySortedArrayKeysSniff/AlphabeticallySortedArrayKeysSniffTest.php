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
    public function testProcessMultiLineArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/MultiLineArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 2);
    }

    public function testProcessMultiLineArraySucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/MultiLineArray.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessMultiLineMixedArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/MultiLineMixedArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    public function testProcessMultiLineMixedArraySucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/MultiLineMixedArray.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessMultiLineMultiDimensionalArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/MultiLineMultiDimensionalArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    public function testProcessMultiLineMultiDimensionalArraySucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/MultiLineMultiDimensionalArray.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessSingleLineArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SingleLineArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 2);
    }

    public function testProcessSingleLineArraySucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SingleLineArray.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessSingleLineMixedArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SingleLineMixedArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    public function testProcessSingleLineMixedArraySucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SingleLineMixedArray.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessSingleLineMultiDimensionalArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SingleLineMultiDimensionalArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    public function testProcessSingleLineMultiDimensionalArraySucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SingleLineMultiDimensionalArray.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testSkipByClassNameSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SkipByClassName.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testSkipByFunctionNameSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SkipByFunctionName.php.inc');
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

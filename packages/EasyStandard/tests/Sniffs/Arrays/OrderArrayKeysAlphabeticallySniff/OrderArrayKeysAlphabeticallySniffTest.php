<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\OrderArrayKeysAlphabeticallySniff;

use EonX\EasyStandard\Sniffs\Arrays\OrderArrayKeysAlphabeticallySniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\Arrays\OrderArrayKeysAlphabeticallySniff
 *
 * @internal
 */
final class OrderArrayKeysAlphabeticallySniffTest extends AbstractCheckerTestCase
{
    public function testProcessMultiLineArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/MultiLineArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 2);
    }

    public function testProcessMultiLineMixedArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/MultiLineMixedArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    public function testProcessMultiLineMultiDimensionalArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/MultiLineMultiDimensionalArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    public function testProcessSingleLineArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SingleLineArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 2);
    }

    public function testProcessSingleLineMixedArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SingleLineMixedArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    public function testProcessSingleLineMultiDimensionalArrayFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SingleLineMultiDimensionalArray.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 4);
    }

    protected function getCheckerClass(): string
    {
        return OrderArrayKeysAlphabeticallySniff::class;
    }

    /**
     * @return mixed[]
     */
    protected function getCheckerConfiguration(): array
    {
        return [
            'skipPatterns' => [
                T_FUNCTION => ['/provide[A-Z]/'],
                T_CLASS => ['/someClass[A-Z]/'],
            ],
        ];
    }
}

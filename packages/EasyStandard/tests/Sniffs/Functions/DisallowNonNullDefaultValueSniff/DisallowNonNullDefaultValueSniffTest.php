<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Functions\DisallowNonNullDefaultValueSniff;

use EonX\EasyStandard\Sniffs\Functions\DisallowNonNullDefaultValueSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\Functions\DisallowNonNullDefaultValueSniff
 *
 * @internal
 */
final class DisallowNonNullDefaultValueSniffTest extends AbstractCheckerTestCase
{
    public function testProcessMultiLineParametersInClosureSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/ClosureMultiLineParameters.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessMultiLineParametersInSimpleFunctionSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SimpleFunctionMultiLineParameters.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessMultiLineParametersInClassMethodSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/ClassMethodMultiLineParameters.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessSingleLineParametersInClosureSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/ClosureSingleLineParameters.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessSingleLineParametersInSimpleFunctionSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SimpleFunctionSingleLineParameters.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessSingleLineParametersInClassMethodSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/ClassMethodSingleLineParameters.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessWrongMultiLineParametersInClassMethodFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/ClassMethodMultiLineParameters.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 10);
    }

    public function testProcessWrongMultiLineParametersInClosureFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/ClosureMultiLineParameters.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 10);
    }

    public function testProcessWrongMultiLineParametersInSimpleFunctionFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SimpleFunctionMultiLineParameters.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 10);
    }

    public function testProcessWrongSingleLineParametersInClassMethodFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/ClassMethodSingleLineParameters.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 3);
    }

    public function testProcessWrongSingleLineParametersInClosureFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/ClosureSingleLineParameters.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 3);
    }

    public function testProcessWrongSingleLineParametersInSimpleFunctionFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SimpleFunctionSingleLineParameters.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 3);
    }

    protected function getCheckerClass(): string
    {
        return DisallowNonNullDefaultValueSniff::class;
    }
}

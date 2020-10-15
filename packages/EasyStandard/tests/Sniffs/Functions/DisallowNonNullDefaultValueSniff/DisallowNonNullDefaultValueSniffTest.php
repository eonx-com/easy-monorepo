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

    public function testProcessMultiLineParametersSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/MultiLineParameters.php.inc');
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

    public function testProcessSingleLineParametersSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/SingleLineParameters.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    public function testProcessWrongMultiLineParametersFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/MultiLineParameters.php.inc');
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

    public function testProcessWrongSingleLineParametersFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/SingleLineParameters.php.inc');
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

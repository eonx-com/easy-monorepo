<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Methods\TestMethodNameSniff;

use EonX\EasyStandard\Sniffs\Methods\TestMethodNameSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\Methods\TestMethodNameSniff
 *
 * @internal
 */
final class TestMethodNameSniffTest extends AbstractCheckerTestCase
{
    /**
     * Tests process a forbidden method name fails.
     */
    public function testProcessForbiddenMethodNameFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/ForbiddenMethodName.php');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 2);
    }

    /**
     * Tests process an ignored method name succeeds.
     */
    public function testProcessIgnoredMethodNameSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/IgnoredMethodName.php');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests process not allowed method name fails.
     */
    public function testProcessNotAllowedMethodNameFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Wrong/NotAllowedMethodName.php');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 2);
    }

    /**
     * Tests process succeeds if a method name conforms with allowed patterns.
     */
    public function testProcessSucceedsIfMethodNameConformWithAllowedPatterns(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/Correct/MethodNameConformsWithAllowedPatterns.php');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests process succeeds if a method name does not conform with forbidden patterns.
     */
    public function testProcessSucceedsIfMethodNameDoesNotConformWithForbiddenPatterns(): void
    {
        $fileInfo = new SmartFileInfo(
            __DIR__ . '/Fixtures/Correct/MethodNameDoesNotConformWithForbiddenPatterns.php'
        );

        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests process a method name succeeds if the namespace does not have forbidden patterns.
     */
    public function testProcessSucceedsIfMethodNameSucceedsIfNamespaceDoesNotHaveForbiddenPatterns(): void
    {
        $fileInfo = new SmartFileInfo(
            __DIR__ . '/Fixtures/Correct/AnotherNamespace/NamespaceDoesNotHaveForbiddenPatterns.php'
        );
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests process a method name succeeds if the namespace does not have allowed patterns.
     */
    public function testProcessSucceedsIfNamespaceDoesNotHaveAllowedPatterns(): void
    {
        $fileInfo = new SmartFileInfo(
            __DIR__ . '/Fixtures/Correct/AnotherNamespace/NamespaceDoesNotHaveAllowedPatterns.php'
        );
        $this->doTestCorrectFileInfo($fileInfo);
    }

    protected function getCheckerClass(): string
    {
        return TestMethodNameSniff::class;
    }

    protected function getCheckerConfiguration(): array
    {
        return [
            'allowed' => [
                [
                    'namespace' => '/^EonX\\\EasyStandard\\\Tests\\\Sniffs\\\Methods\\\TestMethodNameSniff\\\Fixtures\\\(Correct|Wrong)$/',
                    'patterns' => ['/test[A-Z]/', '/test.+(Succeeds|Fails|ThrowsException|DoesNothing)/'],
                ],
            ],
            'forbidden' => [
                [
                    'namespace' => '/^EonX\\\EasyStandard\\\Tests\\\Sniffs\\\Methods\\\TestMethodNameSniff\\\Fixtures\\\(Correct|Wrong)$/',
                    'patterns' => ['/(Succeed|Return|Throw)[^s]/', '/(Successful|SuccessFully)/'],
                ],
            ],
            'ignored' => ['/testIgnoredMethodNameSuccessful/'],
            'testMethodPrefix' => 'test',
        ];
    }
}

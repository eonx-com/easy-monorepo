<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Methods;

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
     * @var string
     */
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    /**
     * Tests process not allowed method name fails.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testProcessNotAllowedMethodNameFails(): void
    {
        $this->doTestFileInfoWithErrorCountOf(
            new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/NotAllowedMethodName.php'),
            2
        );
    }

    /**
     * Tests process a forbidden method name fails.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testProcessForbiddenMethodNameFails(): void
    {
        $this->doTestFileInfoWithErrorCountOf(
            new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/ForbiddenMethodName.php'),
            2
        );
    }

    /**
     * Tests process an ignored method name succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testProcessIgnoredMethodNameSucceeds(): void
    {
        $this->doTestCorrectFileInfo(new SmartFileInfo(self::FIXTURES_DIR . '/Correct/IgnoredMethodName.php'));
    }

    /**
     * Tests process a method name succeeds if the namespace does not have forbidden patterns.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testProcessSucceedsIfMethodNameSucceedsIfNamespaceDoesNotHaveForbiddenPatterns(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/AnotherNamespace/NamespaceDoesNotHaveForbiddenPatterns.php')
        );
    }

    /**
     * Tests process a method name succeeds if the namespace does not have allowed patterns.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testProcessSucceedsIfNamespaceDoesNotHaveAllowedPatterns(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/AnotherNamespace/NamespaceDoesNotHaveAllowedPatterns.php')
        );
    }

    /**
     * Tests process succeeds if a method name conform with allowed patterns.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testProcessSucceedsIfMethodNameConformWithAllowedPatterns(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/MethodNameConformsWithAllowedPatterns.php')
        );
    }

    /**
     * Tests process succeeds if a method name does not conform with forbidden patterns.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testProcessSucceedsIfMethodNameDoesNotConformWithForbiddenPatterns(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/MethodNameDoesNotConformWithForbiddenPatterns.php')
        );
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
                    'namespace' => '/^EonX\\\EasyStandard\\\Tests\\\Sniffs\\\Methods\\\Fixtures\\\(Correct|Wrong)$/',
                    'patterns' => [
                        '/test[A-Z]/',
                        '/test.+(Succeeds|Fails|ThrowsException|DoesNothing)/',
                    ],
                ],
            ],
            'forbidden' => [
                [
                    'namespace' => '/^EonX\\\EasyStandard\\\Tests\\\Sniffs\\\Methods\\\Fixtures\\\(Correct|Wrong)$/',
                    'patterns' => [
                        '/(Succeed|Return|Throw)[^s]/',
                        '/(Successful|SuccessFully)/',
                    ],
                ],
            ],
            'ignored' => [
                '/testIgnoredMethodNameSuccessful/',
            ],
            'testMethodPrefix' => 'test',
        ];
    }
}

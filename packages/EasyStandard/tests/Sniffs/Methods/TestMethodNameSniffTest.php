<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Methods;

use EonX\EasyStandard\Sniffs\Methods\TestMethodNameSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

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
     */
    public function testProcessNotAllowedMethodNameFails(): void
    {
        $this->doTestWrongFile(self::FIXTURES_DIR . '/Wrong/NotAllowedMethodName.php');
    }

    /**
     * Tests process a forbidden method name fails.
     */
    public function testProcessForbiddenMethodNameFails(): void
    {
        $this->doTestWrongFile(self::FIXTURES_DIR . '/Wrong/ForbiddenMethodName.php');
    }

    /**
     * Tests process an ignored method name succeeds.
     */
    public function testProcessIgnoredMethodNameSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/IgnoredMethodName.php');
    }

    /**
     * Tests process a method name succeeds if the namespace does not have forbidden patterns.
     */
    public function testProcessSucceedsIfMethodNameSucceedsIfNamespaceDoesNotHaveForbiddenPatterns(): void
    {
        $this->doTestCorrectFile(
            self::FIXTURES_DIR . '/Correct/AnotherNamespace/NamespaceDoesNotHaveForbiddenPatterns.php'
        );
    }

    /**
     * Tests process a method name succeeds if the namespace does not have allowed patterns.
     */
    public function testProcessSucceedsIfNamespaceDoesNotHaveAllowedPatterns(): void
    {
        $this->doTestCorrectFile(
            self::FIXTURES_DIR . '/Correct/AnotherNamespace/NamespaceDoesNotHaveAllowedPatterns.php'
        );
    }

    /**
     * Tests process succeeds if a method name conform with allowed patterns.
     */
    public function testProcessSucceedsIfMethodNameConformWithAllowedPatterns(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/MethodNameConformsWithAllowedPatterns.php');
    }

    /**
     * Tests process succeeds if a method name does not conform with forbidden patterns.
     */
    public function testProcessSucceedsIfMethodNameDoesNotConformWithForbiddenPatterns(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/MethodNameDoesNotConformWithForbiddenPatterns.php');
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

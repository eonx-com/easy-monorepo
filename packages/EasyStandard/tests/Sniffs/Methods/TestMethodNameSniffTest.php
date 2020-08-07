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
     * Tests process not permitted method name fails.
     */
    public function testProcessNotPermittedMethodNameFails(): void
    {
        $this->doTestWrongFile(self::FIXTURES_DIR . '/Wrong/NotPermittedMethodName.php');
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
     * Tests process a method name succeeds if the namespace does not have permitted patterns.
     */
    public function testProcessSucceedsIfNamespaceDoesNotHavePermittedPatterns(): void
    {
        $this->doTestCorrectFile(
            self::FIXTURES_DIR . '/Correct/AnotherNamespace/NamespaceDoesNotHavePermittedPatterns.php'
        );
    }

    /**
     * Tests process succeeds if a method name conform with permitted patterns.
     */
    public function testProcessSucceedsIfMethodNameConformWithPermittedPatterns(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/MethodNameConformWithPermittedPatterns.php');
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
            'permitted' => [
                [
                    'namespace' => '/^EonX\\\EasyStandard\\\Tests\\\Sniffs\\\Methods\\\Fixtures\\\(Correct|Wrong)$/',
                    'patterns' => [
                        '/test[A-Z]/',
                        '/test.+(Succeeds|Fails|ThrowsException|DoesNothing)/',
                    ],
                ],
            ],
            'testMethodPrefix' => 'test',
        ];
    }
}

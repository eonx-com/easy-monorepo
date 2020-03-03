<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests;

use EonX\EasyTest\Exceptions\UnableToLoadCoverageException;
use EonX\EasyTest\Exceptions\UnableToResolveCoverageException;

final class CheckCoverageCommandTest extends AbstractTestCase
{
    /**
     * DataProvider for testCheckCoverage.
     *
     * @return iterable<mixed>
     */
    public function providerCheckCoverage(): iterable
    {
        yield 'File but coverage too low' => [
            ['file' => __DIR__ . '/fixtures/coverage-70.txt', '--coverage' => 71],
            '[ERROR] Coverage "70%" is lower than expectation "71%"'
        ];

        yield 'File and good coverage' => [
            ['file' => __DIR__ . '/fixtures/coverage-70.txt', '--coverage' => 70],
            '[OK] Yeah nah yeah nah yeah!! Good coverage mate! "70%"'
        ];
    }

    /**
     * DataProvider for testCheckCoverageExceptions.
     *
     * @return iterable<mixed>
     */
    public function providerCheckCoverageExceptions(): iterable
    {
        yield 'File not found' => [
            ['file' => 'invalid-file.txt'],
            UnableToLoadCoverageException::class
        ];

        yield 'File but no coverage' => [
            ['file' => __DIR__ . '/fixtures/no-coverage.txt'],
            UnableToResolveCoverageException::class
        ];

        yield 'File and Lines but no coverage' => [
            ['file' => __DIR__ . '/fixtures/lines-but-no-coverage.txt'],
            UnableToResolveCoverageException::class
        ];
    }

    /**
     * Test check-coverage.
     *
     * @param mixed[] $inputs
     * @param string $expectedOutput
     *
     * @return void
     *
     * @throws \Exception
     *
     * @dataProvider providerCheckCoverage
     */
    public function testCheckCoverage(array $inputs, string $expectedOutput): void
    {
        $output = $this->executeCommand('check-coverage', $inputs);

        self::assertStringContainsString($expectedOutput, $output);
    }

    /**
     * Test check-coverage exceptions.
     *
     * @param mixed[] $inputs
     * @param string $expectedException
     *
     * @return void
     *
     * @throws \Exception
     *
     * @dataProvider providerCheckCoverageExceptions
     *
     * @phpstan-param class-string<\Throwable> $expectedException
     */
    public function testCheckCoverageExceptions(array $inputs, string $expectedException): void
    {
        $this->expectException($expectedException);

        $this->executeCommand('check-coverage', $inputs);
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests;

final class CheckCoverageCommandTest extends AbstractTestCase
{
    /**
     * Check coverage data provider.
     *
     * @return iterable<mixed>
     */
    public function checkCoverageProvider(): iterable
    {
        yield 'File not found' => [
            ['file' => 'invalid-file.txt'],
            '[ERROR] File "invalid-file.txt" not found'
        ];

        yield 'File but no coverage' => [
            ['file' => __DIR__ . '/fixtures/no-coverage.txt'],
            '[ERROR] No coverage found in output'
        ];

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
     * Test check-coverage.
     *
     * @param mixed[] $inputs
     * @param string $expectedOutput
     *
     * @return void
     *
     * @throws \Exception
     *
     * @dataProvider checkCoverageProvider
     */
    public function testCheckCoverage(array $inputs, string $expectedOutput): void
    {
        $output = $this->executeCommand('check-coverage', $inputs);

        self::assertStringContainsString($expectedOutput, $output);
    }
}

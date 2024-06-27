<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Coverage\Command;

use EonX\EasyTest\Coverage\Exception\UnableToLoadCoverageException;
use EonX\EasyTest\Coverage\Exception\UnableToResolveCoverageException;
use EonX\EasyTest\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CheckCoverageCommandTest extends AbstractUnitTestCase
{
    /**
     * @see testCheckCoverage
     */
    public static function providerCheckCoverage(): iterable
    {
        yield 'Txt file but coverage too low' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/coverage-70.txt',
                '--coverage' => 71,
            ],
            '[ERROR] Coverage "70%" is lower than expectation "71%"',
        ];

        yield 'Txf file and good coverage' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/coverage-70.txt',
                '--coverage' => 70,
            ],
            '[OK] Yeah nah yeah nah yeah!! Good coverage mate! "70%"',
        ];

        yield 'Clover file but coverage too low' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/coverage-70.clover',
                '--coverage' => 71,
            ],
            '[ERROR] Coverage "70%" is lower than expectation "71%"',
        ];

        yield 'Clover file but coverage too low and violations are shown' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/coverage-70.clover',
                '--coverage' => 71,
            ],
            '[ERROR] Violations:',
        ];

        yield 'Clover file and good coverage' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/coverage-70.clover',
                '--coverage' => 70,
            ],
            '[OK] Yeah nah yeah nah yeah!! Good coverage mate! "70%"',
        ];
    }

    /**
     * @see testCheckCoverageExceptions
     */
    public static function providerCheckCoverageExceptions(): iterable
    {
        yield 'File not found' => [
            [
                'file' => 'invalid-file.txt',
            ],
            UnableToLoadCoverageException::class,
        ];

        yield 'Txt file but no coverage' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/no-coverage.txt',
            ],
            UnableToResolveCoverageException::class,
        ];

        yield 'Txt file and Lines but no coverage' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/lines-but-no-coverage.txt',
            ],
            UnableToResolveCoverageException::class,
        ];

        yield 'Clover file but wrong format' => [
            [
                'file' => __DIR__ . '/../../../../Fixture/Coverage/wrong-format.clover',
            ],
            UnableToResolveCoverageException::class,
        ];
    }

    /**
     * @throws \Exception
     */
    #[DataProvider('providerCheckCoverage')]
    public function testCheckCoverage(array $inputs, string $expectedOutput): void
    {
        $output = $this->executeCommand('easy-test:check-coverage', $inputs);

        self::assertStringContainsString($expectedOutput, $output);
    }

    /**
     * @phpstan-param class-string<\Throwable> $expectedException
     *
     * @throws \Exception
     */
    #[DataProvider('providerCheckCoverageExceptions')]
    public function testCheckCoverageExceptions(array $inputs, string $expectedException): void
    {
        $this->expectException($expectedException);

        $this->executeCommand('easy-test:check-coverage', $inputs);
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;
use PDOException;
use RuntimeException;
use Throwable;

final class DatabaseErrorScrubberClientConfiguratorTest extends AbstractUnitTestCase
{
    public function testItLeavesNonDatabaseExceptionUntouched(): void
    {
        $client = self::getService(Client::class);

        $client->notifyException(new RuntimeException('Something failed for john@example.com'));

        $report = $this->getFirstReport($client);
        self::assertSame('Something failed for john@example.com', $report->getMessage());
    }

    public function testItReducesDbalExceptionInThePreviousChain(): void
    {
        $client = self::getService(Client::class);

        $driverException = self::driverException(
            "1062 Duplicate entry 'john@example.com' for key 'users.email_unique'",
            '23000'
        );
        $client->notifyException(new RuntimeException('Checkout failed', 0, $driverException));

        $report = $this->getFirstReport($client);
        self::assertSame('Checkout failed', $report->getMessage());

        /** @var \Bugsnag\Report|null $previous */
        $previous = self::getPrivatePropertyValue($report, 'previous');
        self::assertInstanceOf(Report::class, $previous);
        self::assertStringStartsWith('SQLSTATE[23000] ', (string)$previous->getMessage());
        self::assertStringNotContainsString('john@example.com', (string)$previous->getMessage());
    }

    public function testItReducesNativeExceptionBelowDriverException(): void
    {
        $client = self::getService(Client::class);
        $message = "SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value\n"
            . 'DETAIL:  Key (email)=(john@example.com) already exists.';

        $client->notifyException(new UniqueConstraintViolationException(
            self::driverException($message, '23505', new PDOException($message)),
            null
        ));

        $report = $this->getFirstReport($client);
        /** @var \Bugsnag\Report|null $driverReport */
        $driverReport = self::getPrivatePropertyValue($report, 'previous');
        self::assertInstanceOf(Report::class, $driverReport);
        /** @var \Bugsnag\Report|null $nativeReport */
        $nativeReport = self::getPrivatePropertyValue($driverReport, 'previous');
        self::assertInstanceOf(Report::class, $nativeReport);
        self::assertSame('SQLSTATE[23505] ' . PDOException::class, $nativeReport->getMessage());
    }

    public function testItReducesRawDriverException(): void
    {
        $client = self::getService(Client::class);

        $client->notifyException(self::driverException(
            "1062 Duplicate entry 'john@example.com' for key 'users.email_unique'",
            '23000'
        ));

        $report = $this->getFirstReport($client);
        self::assertStringStartsWith('SQLSTATE[23000] ', (string)$report->getMessage());
        self::assertStringNotContainsString('john@example.com', (string)$report->getMessage());
    }

    public function testItReducesWrappedDbalExceptionToSkeleton(): void
    {
        $client = self::getService(Client::class);

        $client->notifyException(new UniqueConstraintViolationException(
            self::driverException(
                "SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value\n"
                . 'DETAIL:  Key (email)=(john@example.com) already exists.',
                '23505'
            ),
            null
        ));

        $report = $this->getFirstReport($client);
        self::assertSame('SQLSTATE[23505] ' . UniqueConstraintViolationException::class, $report->getMessage());
        self::assertStringNotContainsString('john@example.com', (string)$report->getMessage());
    }

    private static function driverException(
        string $message,
        ?string $sqlState,
        ?Throwable $previous = null,
    ): DriverException {
        return new class ($message, $sqlState, $previous) extends RuntimeException implements DriverException {
            public function __construct(
                string $message,
                private readonly ?string $sqlState,
                ?Throwable $previous,
            ) {
                parent::__construct($message, 0, $previous);
            }

            public function getSQLState(): ?string
            {
                return $this->sqlState;
            }
        };
    }

    private function getFirstReport(Client $client): Report
    {
        /** @var \Bugsnag\HttpClient $httpClient */
        $httpClient = self::getPrivatePropertyValue($client, 'http');
        /** @var \Bugsnag\Report[] $reports */
        $reports = self::getPrivatePropertyValue($httpClient, 'queue');

        return $reports[0];
    }
}

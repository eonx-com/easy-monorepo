<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;

final class DatabaseErrorScrubberClientConfiguratorTest extends AbstractUnitTestCase
{
    public function testItKeepsMessagesWithoutLeakUntouched(): void
    {
        $client = self::getService(Client::class);

        $client->notifyError('PDOException', 'SQLSTATE[23505]: Unique violation');

        $report = $this->getFirstReport($client);
        self::assertSame('SQLSTATE[23505]: Unique violation', $report->getMessage());
    }

    public function testItMasksMysqlDuplicateEntryValue(): void
    {
        $client = self::getService(Client::class);

        $client->notifyError(
            'PDOException',
            'SQLSTATE[23000]: Integrity constraint violation: 1062 '
            . "Duplicate entry 'john@example.com' for key 'users.email_unique'"
        );

        $report = $this->getFirstReport($client);
        self::assertStringNotContainsString('john@example.com', (string)$report->getMessage());
        // The key name is not sensitive and must be preserved for debugging
        self::assertStringContainsString("for key 'users.email_unique'", (string)$report->getMessage());
    }

    public function testItMasksMysqlIncorrectValue(): void
    {
        $client = self::getService(Client::class);

        $client->notifyError(
            'PDOException',
            "SQLSTATE[HY000]: 1366 Incorrect integer value: 'abc' for column 'age' at row 1"
        );

        $report = $this->getFirstReport($client);
        self::assertStringNotContainsString("'abc'", (string)$report->getMessage());
        // The type and column name are not sensitive and must be preserved for debugging
        self::assertStringContainsString(
            "Incorrect integer value: '*REDACTED*' for column",
            (string)$report->getMessage()
        );
    }

    public function testItMasksMysqlTruncatedValue(): void
    {
        $client = self::getService(Client::class);

        $client->notifyError('PDOException', "SQLSTATE[22007]: 1292 Truncated incorrect DATE value: '2021-13-45'");

        $report = $this->getFirstReport($client);
        self::assertStringNotContainsString('2021-13-45', (string)$report->getMessage());
        self::assertStringContainsString("Truncated incorrect DATE value: '*REDACTED*'", (string)$report->getMessage());
    }

    public function testItMasksPostgresInvalidInputValue(): void
    {
        $client = self::getService(Client::class);

        $client->notifyError('PDOException', 'SQLSTATE[22P02]: invalid input syntax for type integer: "abc"');

        $report = $this->getFirstReport($client);
        self::assertStringNotContainsString('"abc"', (string)$report->getMessage());
        // The type is not sensitive and must be preserved for debugging
        self::assertStringContainsString(
            'invalid input syntax for type integer: "*REDACTED*"',
            (string)$report->getMessage()
        );
    }

    public function testItStripsPostgresDetailLine(): void
    {
        $client = self::getService(Client::class);

        $client->notifyError(
            'PDOException',
            "SQLSTATE[23505]: Unique violation\nDETAIL:  Failing row contains (1, john@example.com, 1990-01-01)."
        );

        $report = $this->getFirstReport($client);
        self::assertSame('SQLSTATE[23505]: Unique violation', $report->getMessage());
        self::assertStringNotContainsString('DETAIL:', (string)$report->getMessage());
        self::assertStringNotContainsString('john@example.com', (string)$report->getMessage());
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

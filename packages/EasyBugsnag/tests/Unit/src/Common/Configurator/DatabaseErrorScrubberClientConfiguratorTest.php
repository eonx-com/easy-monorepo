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

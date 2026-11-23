<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Throwable;

final class DatabaseErrorScrubberClientConfiguratorTest extends AbstractUnitTestCase
{
    /**
     * @see testItReducesJsonEncodedCopiesOfMessageInText
     */
    public static function provideMessagesThatJsonEscapes(): iterable
    {
        yield 'multiline PostgreSQL message' => [
            "SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value\n"
            . 'DETAIL:  Key (email)=(john@example.com) already exists.',
        ];

        yield 'message with a slash' => ["SQLSTATE[23505]: Unique violation: key 'john@example.com/1' already exists"];
    }

    public function testItKeepsJsonInMetadataDecodableForSensitiveDataSanitizer(): void
    {
        self::bootKernel(['environment' => 'error_details']);
        $client = self::getService(Client::class);
        $throwable = new UniqueConstraintViolationException(
            self::driverException("1062 Duplicate entry 'john@example.com' for key 'users.email_unique'", '23000'),
            null
        );
        $report = Report::fromPHPThrowable($client->getConfig(), $throwable);
        $report->setMetaData([
            'context' => [
                'payload' => \json_encode([
                    'api_key' => 'some-api-key',
                    'error' => $throwable->getMessage(),
                    'note' => 'a "quoted" word',
                ]),
            ],
        ]);

        $client->notify($report);

        $payload = $this->getFirstReport($client)
            ->toArray()['metaData']['context']['payload'];
        self::assertIsString($payload);
        self::assertSame(
            [
                'api_key' => '*REDACTED*',
                'error' => 'SQLSTATE[23000] UniqueConstraintViolationException',
                'note' => 'a "quoted" word',
            ],
            \json_decode($payload, true)
        );
    }

    public function testItKeepsJsonInTextDecodableWhenMessageEndsWithBackslash(): void
    {
        self::bootKernel(['environment' => 'error_details']);
        $client = self::getService(Client::class);
        $throwable = new UniqueConstraintViolationException(
            self::driverException('SQLSTATE[HY000]: General error: 1 no such table: users\\', 'HY000'),
            null
        );
        $report = Report::fromPHPThrowable($client->getConfig(), $throwable);
        $report->setMetaData([
            'context' => [
                'payload' => 'Sync failed: ' . \json_encode([
                    'api_key' => 'some-api-key',
                    'error' => $throwable->getMessage(),
                    'note' => 'a "quoted" word',
                ]),
            ],
        ]);

        $client->notify($report);

        $payload = $this->getFirstReport($client)
            ->toArray()['metaData']['context']['payload'];
        self::assertIsString($payload);
        self::assertSame(
            [
                'api_key' => '*REDACTED*',
                'error' => 'SQLSTATE[HY000] UniqueConstraintViolationException',
                'note' => 'a "quoted" word',
            ],
            \json_decode(\substr($payload, \strlen('Sync failed: ')), true)
        );
    }

    public function testItLeavesNonDatabaseExceptionUntouched(): void
    {
        $client = self::getService(Client::class);

        $client->notifyException(new RuntimeException('Something failed for john@example.com'));

        $report = $this->getFirstReport($client);
        self::assertSame('Something failed for john@example.com', $report->getMessage());
    }

    public function testItReducesDbMessagesEmbeddedInMessagesAboveDriverException(): void
    {
        self::bootKernel(['environment' => 'error_details']);
        $client = self::getService(Client::class);
        $dbalException = new UniqueConstraintViolationException(
            self::driverException("1062 Duplicate entry 'john@example.com' for key 'users.email_unique'", '23000'),
            null
        );

        $client->notifyException(
            new RuntimeException('Checkout failed: ' . $dbalException->getMessage(), 0, $dbalException)
        );

        $reportData = $this->getFirstReport($client)
            ->toArray();
        $message = 'Checkout failed: SQLSTATE[23000] UniqueConstraintViolationException';
        self::assertSame($message, $reportData['exceptions'][0]['message']);
        self::assertSame($message, $reportData['metaData']['error']['message']);
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
        self::assertStringStartsWith('SQLSTATE[23000] ', (string) $previous->getMessage());
        self::assertStringNotContainsString('john@example.com', (string) $previous->getMessage());
    }

    #[DataProvider('provideMessagesThatJsonEscapes')]
    public function testItReducesJsonEncodedCopiesOfMessageInText(string $message): void
    {
        self::bootKernel(['environment' => 'error_details']);
        $client = self::getService(Client::class);
        $throwable = new UniqueConstraintViolationException(self::driverException($message, '23505'), null);
        $report = Report::fromPHPThrowable($client->getConfig(), $throwable);
        $report->setMetaData([
            'context' => [
                'payload' => 'Sync failed: ' . \json_encode([
                    'error' => $throwable->getMessage(),
                ]),
            ],
        ]);

        $client->notify($report);

        $payload = $this->getFirstReport($client)
            ->toArray()['metaData']['context']['payload'];
        self::assertIsString($payload);
        self::assertSame(
            [
                'error' => 'SQLSTATE[23505] UniqueConstraintViolationException',
            ],
            \json_decode(\substr($payload, \strlen('Sync failed: ')), true)
        );
    }

    public function testItReducesMessageInJsonEncodedWithOtherFlags(): void
    {
        self::bootKernel(['environment' => 'error_details']);
        $client = self::getService(Client::class);
        $throwable = new UniqueConstraintViolationException(
            self::driverException("1062 Duplicate entry 'john@example.com' for key 'users.email_unique'", '23000'),
            null
        );
        $report = Report::fromPHPThrowable($client->getConfig(), $throwable);
        $report->setMetaData([
            'context' => [
                'payload' => \json_encode([
                    'error' => $throwable->getMessage(),
                ], \JSON_HEX_APOS | \JSON_HEX_QUOT),
            ],
        ]);

        $client->notify($report);

        $payload = $this->getFirstReport($client)
            ->toArray()['metaData']['context']['payload'];
        self::assertIsString($payload);
        self::assertSame(
            [
                'error' => 'SQLSTATE[23000] UniqueConstraintViolationException',
            ],
            \json_decode($payload, true)
        );
    }

    public function testItReducesMessageInObjectMetadata(): void
    {
        self::bootKernel(['environment' => 'error_details']);
        $client = self::getService(Client::class);
        $throwable = new UniqueConstraintViolationException(
            self::driverException("1062 Duplicate entry 'john@example.com' for key 'users.email_unique'", '23000'),
            null
        );
        $report = Report::fromPHPThrowable($client->getConfig(), $throwable);
        $report->setMetaData([
            'context' => [
                'details' => (object) [
                    'message' => $throwable->getMessage(),
                ],
            ],
        ]);

        $client->notify($report);

        self::assertSame(
            [
                'message' => 'SQLSTATE[23000] UniqueConstraintViolationException',
            ],
            $this->getFirstReport($client)
                ->toArray()['metaData']['context']['details']
        );
    }

    public function testItReducesMessagesRestoredAndCopiedByOtherConfigurators(): void
    {
        self::bootKernel(['environment' => 'error_details']);
        $client = self::getService(Client::class);
        $message = "SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value\n"
            . 'DETAIL:  Key (email)=(john@example.com) already exists.';

        $client->notifyException(new UniqueConstraintViolationException(
            self::driverException($message, '23505', new PDOException($message)),
            null
        ));

        $reportData = $this->getFirstReport($client)
            ->toArray();
        $dbalMessage = 'SQLSTATE[23505] UniqueConstraintViolationException';
        self::assertSame($dbalMessage, $reportData['exceptions'][0]['message']);
        self::assertSame('SQLSTATE[23505] PDOException', $reportData['exceptions'][2]['message']);
        self::assertSame($dbalMessage, $reportData['metaData']['error']['message']);
        // The driver exception and the PDOException have the same message, so their copies get no class
        self::assertSame('SQLSTATE[23505]', $reportData['metaData']['error']['previous_1']['message']);
        self::assertSame('SQLSTATE[23505]', $reportData['metaData']['error']['previous_2']['message']);
        $messages = \array_column($reportData['exceptions'], 'message');
        self::assertStringNotContainsString('john@example.com', (string) \json_encode($messages));
        self::assertStringNotContainsString('john@example.com', (string) \json_encode($reportData['metaData']));
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
        self::assertSame('SQLSTATE[23505] PDOException', $nativeReport->getMessage());
    }

    public function testItReducesRawDriverException(): void
    {
        $client = self::getService(Client::class);

        $client->notifyException(self::driverException(
            "1062 Duplicate entry 'john@example.com' for key 'users.email_unique'",
            '23000'
        ));

        $report = $this->getFirstReport($client);
        self::assertStringStartsWith('SQLSTATE[23000] ', (string) $report->getMessage());
        self::assertStringNotContainsString('john@example.com', (string) $report->getMessage());
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
        self::assertSame('SQLSTATE[23505] UniqueConstraintViolationException', $report->getMessage());
        self::assertStringNotContainsString('john@example.com', (string) $report->getMessage());
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

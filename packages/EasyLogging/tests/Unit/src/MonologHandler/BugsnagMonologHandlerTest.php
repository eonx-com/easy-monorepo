<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\MonologHandler;

use Bugsnag\Client;
use Bugsnag\Configuration;
use DateTimeImmutable;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolver;
use EonX\EasyLogging\Tests\Unit\AbstractSymfonyTestCase;
use Monolog\Formatter\LineFormatter;

final class BugsnagMonologHandlerTest extends AbstractSymfonyTestCase
{
    public function testItSucceeds(): void
    {
        $client = new Client(new Configuration('some-api-key'));
        $sut = new BugsnagMonologHandler(new BugsnagSeverityResolver(), $client);
        $sut->setFormatter(new LineFormatter('formatted'));

        $sut->handle([
            'channel' => 'app',
            'context' => [],
            'datetime' => new DateTimeImmutable(),
            'extra' => [],
            'formatted' => 'formatted',
            'level' => 300,
            'level_name' => 'WARNING',
            'message' => 'message',
        ]);

        $reports = self::getPrivatePropertyValue(
            self::getPrivatePropertyValue($client, 'http'),
            'queue'
        );
        self::assertCount(1, $reports);
        /** @var \Bugsnag\Report $report */
        $report = $reports[0];
        self::assertSame('info', $report->getSeverity());
        self::assertSame('message', $report->getName());
        self::assertSame('formatted', $report->getMessage());
    }

    public function testItSucceedsAndDoNothingWithExceptionHandledByEasyErrorHandler(): void
    {
        $client = new Client(new Configuration('some-api-key'));
        $sut = new BugsnagMonologHandler(new BugsnagSeverityResolver(), $client);
        $sut->setFormatter(new LineFormatter('formatted'));

        $sut->handle([
            'channel' => 'app',
            'context' => [
                'exception_reported_by_error_handler' => true,
            ],
            'datetime' => new DateTimeImmutable(),
            'extra' => [],
            'formatted' => 'formatted',
            'level' => 300,
            'level_name' => 'WARNING',
            'message' => 'message',
        ]);

        $reports = self::getPrivatePropertyValue(
            self::getPrivatePropertyValue($client, 'http'),
            'queue'
        );
        self::assertEmpty($reports);
    }
}

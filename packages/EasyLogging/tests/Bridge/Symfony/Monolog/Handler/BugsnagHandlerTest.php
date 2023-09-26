<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Symfony\Monolog\Handler;

use Bugsnag\Client;
use Bugsnag\Configuration;
use DateTimeImmutable;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Handler\BugsnagHandler;
use EonX\EasyLogging\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Monolog\Formatter\LineFormatter;
use Symfony\Component\DependencyInjection\Container;

final class BugsnagHandlerTest extends AbstractSymfonyTestCase
{
    public function testItSucceeds(): void
    {
        $client = new Client(new Configuration('some-api-key'));
        $container = new Container();
        $container->set(Client::class, $client);
        $sut = new BugsnagHandler();
        $sut->setContainer($container);
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

        $reports = $this->getPrivatePropertyValue(
            $this->getPrivatePropertyValue($client, 'http'),
            'queue'
        );
        self::assertCount(1, $reports);
        /** @var \Bugsnag\Report $report */
        $report = $reports[0];
        self::assertSame('info', $report->getSeverity());
        self::assertSame('message', $report->getName());
        self::assertSame('formatted', $report->getMessage());
    }
}

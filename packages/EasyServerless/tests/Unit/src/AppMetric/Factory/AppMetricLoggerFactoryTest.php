<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\AppMetric\Factory;

use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Factory\AppMetricLoggerFactory;
use EonX\EasyServerless\AppMetric\ValueObject\AppMetric;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class AppMetricLoggerFactoryTest extends AbstractUnitTestCase
{
    public function testCreateReturnsLoggerWritingDebugRecordsToStderrByDefault(): void
    {
        $logger = (new AppMetricLoggerFactory())->create();

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame(AppMetricLoggerFactory::LOGGER_NAME, $logger->getName());
        self::assertCount(1, $logger->getHandlers());
        $handler = $logger->getHandlers()[0];
        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertSame('php://stderr', $handler->getUrl());
        self::assertSame(Level::Debug, $handler->getLevel());
    }

    public function testCreatedLoggerWritesAppMetricAsJson(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $client = new AppMetricClient((new AppMetricLoggerFactory())->create($stream), 'my_namespace');

        $client->sendMetric(new AppMetric('my_metric', [
            'my_dimension' => 'my_value',
        ]));

        \rewind($stream);
        $output = (string)\stream_get_contents($stream);
        self::assertStringEndsWith("\n", $output);
        /** @var array{message: string, level_name: string, channel: string, context: array<string, array<string, string|array<string, string>>>, extra: array<string, string>} $record */
        $record = \json_decode($output, true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame('[appMetric][my_namespace_my_metric]', $record['message']);
        self::assertSame('DEBUG', $record['level_name']);
        self::assertSame(AppMetricLoggerFactory::LOGGER_NAME, $record['channel']);
        self::assertSame([
            'appMetric' => [
                'dimensions' => [
                    'my_namespace_my_dimension' => 'my_value',
                ],
                'name' => 'my_metric',
                'namespace' => 'my_namespace',
            ],
        ], $record['context']);
        self::assertSame('php', $record['extra']['source']);
    }

    public function testIsSupportedWithMonolog3(): void
    {
        self::assertTrue(AppMetricLoggerFactory::isSupported());
    }
}

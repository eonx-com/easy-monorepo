<?php
declare(strict_types=1);

namespace EonX\EasyServerless\AppMetric\Factory;

use EonX\EasyServerless\Monolog\Processor\PhpSourceProcessor;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final readonly class AppMetricLoggerFactory
{
    public const LOGGER_NAME = 'easy-serverless.app_metric';

    private const DEFAULT_STREAM = 'php://stderr';

    /**
     * App metrics are generated from logs, so they must not depend on the application logging configuration
     * (handlers, levels, channels, sampling, etc.) which could drop them. This logger is self-contained and always
     * writes every record as JSON to the given stream.
     *
     * @param resource|string $stream
     */
    public function create(mixed $stream = self::DEFAULT_STREAM): LoggerInterface
    {
        $streamHandler = new StreamHandler($stream, Level::Debug);
        $streamHandler->setFormatter(new JsonFormatter());

        return new Logger(self::LOGGER_NAME, [$streamHandler], [new PhpSourceProcessor()]);
    }
}

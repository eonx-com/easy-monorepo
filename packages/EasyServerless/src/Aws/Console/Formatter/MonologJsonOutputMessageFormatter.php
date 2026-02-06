<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Formatter;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Monolog\Processor\PhpSourceProcessor;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class MonologJsonOutputMessageFormatter implements OutputMessageFormatterInterface
{
    private const string LOGGER_NAME = 'easy-serverless.console.json_output';

    public function format(string $message): string
    {
        if (LambdaContextHelper::inLambda() === false || $message === '' || $message === \PHP_EOL) {
            return $message;
        }

        // Using Monolog to format messages as JSON might seem a bit overkill, but it allows console output messages
        // to be linked with traces in monitoring tools like Datadog when auto-instrumentation is used

        $streamHandler = new StreamHandler('php://memory');
        $streamHandler->setFormatter(new JsonFormatter());

        new Logger(self::LOGGER_NAME, [$streamHandler], [new PhpSourceProcessor()])->debug($message);

        $stream = $streamHandler->getStream();
        if (\is_resource($stream) === false) {
            return $message;
        }

        \rewind($stream);
        $jsonMessage = \stream_get_contents($stream);
        \fclose($stream);

        return \is_string($jsonMessage) ? $jsonMessage : $message;
    }
}

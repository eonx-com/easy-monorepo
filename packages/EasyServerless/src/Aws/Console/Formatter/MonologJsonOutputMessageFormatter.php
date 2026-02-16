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
        $trimmedMessage = \trim($message);

        if (LambdaContextHelper::inLambda() === false || $trimmedMessage === '' || $trimmedMessage === \PHP_EOL) {
            return $message; // Return original message on purpose so it doesn't cause side effects
        }

        // Using Monolog to format messages as JSON might seem a bit overkill, but it allows console output messages
        // to be linked with traces in monitoring tools like Datadog when auto-instrumentation is used

        $streamHandler = new StreamHandler('php://memory');
        $streamHandler->setFormatter(new JsonFormatter());

        (new Logger(self::LOGGER_NAME, [$streamHandler], [new PhpSourceProcessor()]))->debug($trimmedMessage);

        $stream = $streamHandler->getStream();
        if (\is_resource($stream) === false) {
            return $trimmedMessage;
        }

        \rewind($stream);
        $jsonMessage = \stream_get_contents($stream);
        \fclose($stream);

        return \is_string($jsonMessage) ? $jsonMessage : $trimmedMessage;
    }
}

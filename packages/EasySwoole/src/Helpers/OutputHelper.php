<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasySwoole\Bridge\Monolog\SimpleFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class OutputHelper
{
    private const PREFIX = '[php.swoole]';

    private const STREAM = 'php://stdout';

    /**
     * @var callable|null
     */
    private static $writer;

    public static function writeln(string $message): void
    {
        if (self::$writer === null) {
            self::init();
        }

        (self::$writer)($message);
    }

    private static function init(): void
    {
        if (\class_exists(Logger::class)) {
            $logger = new Logger(self::PREFIX, [
                (new StreamHandler(self::STREAM))->setFormatter(new SimpleFormatter(self::PREFIX)),
            ]);

            self::$writer = static function (string $message) use ($logger): void {
                $logger->debug($message);
            };

            return;
        }

        self::$writer = static function (string $message): void {
            $stream = \fopen(self::STREAM, 'w+');

            if (\is_resource($stream)) {
                \fwrite($stream, \sprintf('%s %s' . \PHP_EOL, self::PREFIX, $message));
                \fclose($stream);
            }
        };
    }
}

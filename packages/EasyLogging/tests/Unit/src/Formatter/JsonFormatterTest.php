<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Formatter;

use Carbon\Carbon;
use EonX\EasyLogging\Formatter\JsonFormatter;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;

final class JsonFormatterTest extends AbstractUnitTestCase
{
    /**
     * @see testCreateLogFormat
     */
    public static function provideCreateLogFormatData(): iterable
    {
        yield 'DateTime format' => [
            static function (Logger $logger): void {
                $logger->debug('my-message', [
                    'carbon' => Carbon::createFromFormat('dmY', '06081993'),
                ]);
            },
            static function (string $logContents): void {
                self::assertJson($logContents);

                /** @var array $json */
                $json = \json_decode($logContents, true);

                self::assertInstanceOf(
                    Carbon::class,
                    Carbon::createFromFormat('Y-m-d\TH:i:sP', $json['context']['carbon'])
                );
            },
        ];
    }

    /**
     * @throws \Exception
     */
    #[DataProvider('provideCreateLogFormatData')]
    public function testCreateLogFormat(callable $log, callable $assert): void
    {
        $stream = \fopen('php://memory', 'rw+');

        if (\is_resource($stream) === false) {
            return;
        }

        $handler = new StreamHandler($stream)
->setFormatter(new JsonFormatter());
        $logger = new Logger('test', [$handler]);

        $log($logger);

        \rewind($stream);
        $logContents = (string)\fread($stream, 2048);
        \fclose($stream);

        $assert($logContents);
    }
}

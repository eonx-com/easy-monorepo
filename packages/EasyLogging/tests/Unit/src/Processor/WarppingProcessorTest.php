<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Processor;

use DateTimeImmutable;
use EonX\EasyLogging\Processor\WarppingProcessor;
use EonX\EasyLogging\Tests\Stub\ValueObject\InvokableStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;

final class WarppingProcessorTest extends AbstractUnitTestCase
{
    /**
     * @see testInvoke
     */
    public static function provideInvokeData(): iterable
    {
        yield 'Using closure' => [
            fn (LogRecord $record): LogRecord => $record,
        ];

        yield 'Using object with __invoke method' => [new InvokableStub()];
    }

    #[DataProvider('provideInvokeData')]
    public function testInvoke(callable $wrapped): void
    {
        $wrapper = WarppingProcessor::wrap($wrapped);
        $logRecord = new LogRecord(new DateTimeImmutable(), 'some-channel', Level::Warning, 'some-message');

        self::assertEquals($logRecord, $wrapper($logRecord));
    }
}

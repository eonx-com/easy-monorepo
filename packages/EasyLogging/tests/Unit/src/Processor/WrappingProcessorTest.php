<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Processor;

use EonX\EasyLogging\Processor\WrappingProcessor;
use EonX\EasyLogging\Tests\Stub\ValueObject\InvokableStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-import-type Record from \Monolog\Logger
 */
final class WrappingProcessorTest extends AbstractUnitTestCase
{
    /**
     * @see testInvoke
     */
    public static function provideInvokeData(): iterable
    {
        yield 'Using closure' => [
            fn (array $records): array => $records,
        ];

        yield 'Using object with __invoke method' => [new InvokableStub()];
    }

    #[DataProvider('provideInvokeData')]
    public function testInvoke(callable $wrapped): void
    {
        $wrapper = WrappingProcessor::wrap($wrapped);
        /** @phpstan-var Record $array */
        $array = [
            'key' => 'value',
        ];

        self::assertEquals($array, $wrapper($array));
    }
}

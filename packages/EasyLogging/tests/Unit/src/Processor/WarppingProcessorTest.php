<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Processor;

use EonX\EasyLogging\Processor\WarppingProcessor;
use EonX\EasyLogging\Tests\Stub\ValueObject\InvokableStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-import-type Record from \Monolog\Logger
 */
final class WarppingProcessorTest extends AbstractUnitTestCase
{
    /**
     * @see testInvoke
     */
    public static function providerTestInvoke(): iterable
    {
        yield 'Using closure' => [
            fn (array $records): array => $records,
        ];

        yield 'Using object with __invoke method' => [new InvokableStub()];
    }

    #[DataProvider('providerTestInvoke')]
    public function testInvoke(callable $wrapped): void
    {
        $wrapper = WarppingProcessor::wrap($wrapped);
        /** @phpstan-var Record $array */
        $array = [
            'key' => 'value',
        ];

        self::assertEquals($array, $wrapper($array));
    }
}

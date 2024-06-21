<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Processor;

use EonX\EasyLogging\Tests\Stub\Processor\SelfConfigProvidingProcessorStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;

final class SelfConfigProvidingProcessorTest extends AbstractUnitTestCase
{
    public function testSanity(): void
    {
        $selfProcessorConfigProvider = new SelfConfigProvidingProcessorStub();

        /** @var \Generator<\EonX\EasyLogging\Config\ProcessorConfigInterface> $processors */
        $processors = $selfProcessorConfigProvider->processors();
        $processors = \iterator_to_array($processors);

        self::assertCount(1, $processors);
        self::assertSame($selfProcessorConfigProvider, $processors[0]->processor());
        self::assertEquals(0, $processors[0]->getPriority());
    }
}

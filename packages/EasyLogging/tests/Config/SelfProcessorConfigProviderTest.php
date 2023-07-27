<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Config;

use EonX\EasyLogging\Tests\AbstractTestCase;
use EonX\EasyLogging\Tests\Stubs\SelfProcessorConfigProviderStub;

final class SelfProcessorConfigProviderTest extends AbstractTestCase
{
    public function testSanity(): void
    {
        $selfProcessorConfigProvider = new SelfProcessorConfigProviderStub();

        /** @var \Generator<\EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface> $processors */
        $processors = $selfProcessorConfigProvider->processors();
        $processors = \iterator_to_array($processors);

        self::assertCount(1, $processors);
        self::assertSame($selfProcessorConfigProvider, $processors[0]->processor());
        self::assertEquals(0, $processors[0]->getPriority());
    }
}

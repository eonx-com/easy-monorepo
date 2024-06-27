<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Stub\Processor;

use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;

final class SelfConfigProvidingProcessorStub extends AbstractSelfConfigProvidingProcessor
{
    public function __invoke(array $record): array
    {
        return $record;
    }
}

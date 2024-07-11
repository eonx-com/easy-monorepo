<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Stub\Processor;

use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;
use Monolog\LogRecord;

final class SelfConfigProvidingProcessorStub extends AbstractSelfConfigProvidingProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return $record;
    }
}

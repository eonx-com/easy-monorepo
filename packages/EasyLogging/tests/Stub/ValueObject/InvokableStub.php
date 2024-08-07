<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Stub\ValueObject;

use Monolog\LogRecord;

final class InvokableStub
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return $record;
    }
}

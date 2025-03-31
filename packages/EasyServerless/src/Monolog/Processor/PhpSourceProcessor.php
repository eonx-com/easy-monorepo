<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class PhpSourceProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        // This is helping logs processing in Datadog
        $record->extra['source'] = 'php';

        return $record;
    }
}

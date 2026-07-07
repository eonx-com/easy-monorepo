<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Monolog\Processor;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;

#[AsMonologProcessor]
final class PhpSourceProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        // This is helping logs processing in Datadog
        $record->extra['source'] = 'php';

        return $record;
    }
}

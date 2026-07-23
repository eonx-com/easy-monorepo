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
        $extra = $record->extra;
        // This is helping logs processing in Datadog
        $extra['source'] = 'php';

        return $record->with(extra: $extra);
    }
}

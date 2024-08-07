<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Logging\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

final readonly class SimpleFormatter implements FormatterInterface
{
    public function __construct(
        private string $prefix,
    ) {
    }

    public function format(LogRecord $record): string
    {
        return \sprintf('%s %s' . \PHP_EOL, $this->prefix, $record->message);
    }

    public function formatBatch(array $records): string
    {
        $message = '';

        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }
}

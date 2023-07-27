<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Monolog;

use Monolog\Formatter\FormatterInterface;

final class SimpleFormatter implements FormatterInterface
{
    public function __construct(
        private readonly string $prefix,
    ) {
    }

    public function format(array $record): string
    {
        return \sprintf('%s %s' . \PHP_EOL, $this->prefix, $record['message']);
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

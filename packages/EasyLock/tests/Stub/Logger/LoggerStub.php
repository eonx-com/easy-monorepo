<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Stub\Logger;

use Psr\Log\AbstractLogger;
use Stringable;

final class LoggerStub extends AbstractLogger
{
    /**
     * @var array<int, array{level: string, message: string|\Stringable, context: array}>
     */
    private array $records = [];

    /**
     * @return array<int, array{level: string, message: string|\Stringable, context: array}>
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'context' => $context,
            'level' => \is_string($level) ? $level : \get_debug_type($level),
            'message' => $message,
        ];
    }
}

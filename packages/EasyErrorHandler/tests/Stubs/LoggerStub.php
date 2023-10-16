<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Stubs;

use Psr\Log\AbstractLogger;
use Stringable;

final class LoggerStub extends AbstractLogger
{
    /**
     * @var list<array<string, mixed>>
     */
    private array $records;

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function getRecords(): array
    {
        return $this->records;
    }
}

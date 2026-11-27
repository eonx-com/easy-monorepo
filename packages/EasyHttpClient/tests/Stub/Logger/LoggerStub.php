<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Stub\Logger;

use Psr\Log\AbstractLogger;
use Stringable;

final class LoggerStub extends AbstractLogger
{
    /**
     * @var array<int, array{context: array, message: string|\Stringable}>
     */
    private array $records = [];

    /**
     * @return array<int, array{context: array, message: string|\Stringable}>
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'context' => $context,
            'message' => $message,
        ];
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Fixture\App\Processor;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use RuntimeException;

#[AsMonologProcessor]
final class FailingProcessor
{
    public const HEADER = 'X-Failing-Logger';

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $record->context['request'] ?? null;

        if (\is_array($request) && \is_array($request['headers'] ?? null)
            && \array_key_exists(\strtolower(self::HEADER), $request['headers'])) {
            throw new RuntimeException('Some logger failure.');
        }

        return $record;
    }
}

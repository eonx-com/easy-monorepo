<?php
declare(strict_types=1);

namespace EonX\EasyTest\Monolog\Processor;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;

#[AsMonologProcessor(priority: -10000)]
final class LogCollectorProcessor
{
    /**
     * @var \Monolog\LogRecord[]
     */
    private static array $records = [];

    public function __invoke(LogRecord $record): LogRecord
    {
        self::$records[] = $record;

        return $record;
    }

    /**
     * @return \Monolog\LogRecord[]
     */
    public static function getRecords(): array
    {
        return self::$records;
    }

    public static function reset(): void
    {
        self::$records = [];
    }
}

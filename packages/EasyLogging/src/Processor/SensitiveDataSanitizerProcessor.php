<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Processor;

use EonX\EasyLogging\Config\ProcessorConfigInterface;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Monolog\Level;
use Monolog\LogRecord;

final class SensitiveDataSanitizerProcessor extends AbstractSelfConfigProvidingProcessor
{
    public function __construct(
        private readonly SensitiveDataSanitizerInterface $sensitiveDataSanitizer,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $recordData = $this->sensitiveDataSanitizer->sanitize($record->toArray());
        unset($recordData['level_name']);
        $recordData['level'] = Level::fromValue($recordData['level']);

        return $record->with(...$recordData);
    }

    protected function configure(ProcessorConfigInterface $processorConfig): void
    {
        $processorConfig->priority(\PHP_INT_MAX);
    }
}

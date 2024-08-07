<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Processor;

use EonX\EasyLogging\Config\ProcessorConfigInterface;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Monolog\LogRecord;

final class SensitiveDataSanitizerProcessor extends AbstractSelfConfigProvidingProcessor
{
    public function __construct(
        private readonly SensitiveDataSanitizerInterface $sensitiveDataSanitizer,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with($this->sensitiveDataSanitizer->sanitize($record->toArray()));
    }

    protected function configure(ProcessorConfigInterface $processorConfig): void
    {
        $processorConfig->priority(\PHP_INT_MAX);
    }
}

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
        $sanitizedData = $this->sensitiveDataSanitizer->sanitize([
            'context' => $record->context,
            'extra' => $record->extra,
            'formatted' => $record->formatted,
            'message' => $record->message,
        ]);

        return $record->with(...$sanitizedData);
    }

    protected function configure(ProcessorConfigInterface $processorConfig): void
    {
        $processorConfig->priority(\PHP_INT_MAX);
    }
}

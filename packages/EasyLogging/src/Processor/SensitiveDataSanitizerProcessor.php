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
        $recordData = $record->toArray();
        $sanitizedData = $this->sensitiveDataSanitizer->sanitize([
            'context' => $recordData['context'],
            'extra' => $recordData['extra'],
            'message' => $recordData['message'],
        ]);

        return $record->with(
            message: $sanitizedData['message'],
            context: $sanitizedData['context'],
            level: Level::fromValue($recordData['level']),
            channel: $recordData['channel'],
            datetime: $recordData['datetime'],
            extra: $sanitizedData['extra']
        );
    }

    protected function configure(ProcessorConfigInterface $processorConfig): void
    {
        $processorConfig->priority(\PHP_INT_MAX);
    }
}

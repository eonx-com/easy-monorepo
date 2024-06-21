<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Processor;

use EonX\EasyLogging\Config\ProcessorConfigInterface;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;

final class SensitiveDataSanitizerProcessor extends AbstractSelfConfigProvidingProcessor
{
    public function __construct(
        private readonly SensitiveDataSanitizerInterface $sensitiveDataSanitizer,
    ) {
    }

    public function __invoke(array $record): array
    {
        return $this->sensitiveDataSanitizer->sanitize($record);
    }

    protected function configure(ProcessorConfigInterface $processorConfig): void
    {
        $processorConfig->priority(\PHP_INT_MAX);
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\EasyUtils;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;

final class SensitiveDataSanitizerProcessor extends AbstractSelfProcessorConfigProvider
{
    public function __construct(
        private readonly SensitiveDataSanitizerInterface $sensitiveDataSanitizer,
    ) {
    }

    /**
     * @param mixed[] $record
     *
     * @return mixed[]
     */
    public function __invoke(array $record): array
    {
        return $this->sensitiveDataSanitizer->sanitize($record);
    }

    protected function configure(ProcessorConfigInterface $processorConfig): void
    {
        $processorConfig->priority(\PHP_INT_MAX);
    }
}

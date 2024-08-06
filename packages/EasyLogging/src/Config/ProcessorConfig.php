<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use Monolog\Processor\ProcessorInterface;

final class ProcessorConfig extends AbstractLoggingConfig implements ProcessorConfigInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
    ) {
    }

    public static function create(ProcessorInterface $processor): self
    {
        return new self($processor);
    }

    public function processor(): ProcessorInterface
    {
        return $this->processor;
    }
}

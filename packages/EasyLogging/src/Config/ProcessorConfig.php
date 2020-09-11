<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface;
use Monolog\Processor\ProcessorInterface;

final class ProcessorConfig extends AbstractLoggingConfig implements ProcessorConfigInterface
{
    /**
     * @var \Monolog\Processor\ProcessorInterface
     */
    private $processor;

    public function __construct(ProcessorInterface $processor)
    {
        $this->processor = $processor;
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

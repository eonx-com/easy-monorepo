<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface;
use Monolog\Processor\ProcessorInterface;

final class ProcessorConfig extends AbstractLoggingConfig implements ProcessorConfigInterface
{
    /**
     * @var \EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface
     */
    private $processor;

    /**
     * @param null|string[] $channels
     */
    public function __construct(ProcessorInterface $processor, ?array $channels = null, ?int $priority = null)
    {
        $this->processor = $processor;

        parent::__construct($channels, $priority);
    }

    public function processor(): ProcessorInterface
    {
        return $this->processor;
    }
}

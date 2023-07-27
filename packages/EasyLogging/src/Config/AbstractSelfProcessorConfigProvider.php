<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigProviderInterface;
use Monolog\Processor\ProcessorInterface;

abstract class AbstractSelfProcessorConfigProvider implements ProcessorConfigProviderInterface, ProcessorInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface>
     */
    public function processors(): iterable
    {
        $processorConfig = ProcessorConfig::create($this);

        // Allow children classes to configure channels
        $this->configure($processorConfig);

        yield $processorConfig;
    }

    protected function configure(ProcessorConfigInterface $processorConfig): void
    {
        // No body needed
    }
}

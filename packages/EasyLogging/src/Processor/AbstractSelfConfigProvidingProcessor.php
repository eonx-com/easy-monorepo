<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Processor;

use EonX\EasyLogging\Config\ProcessorConfig;
use EonX\EasyLogging\Config\ProcessorConfigInterface;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use Monolog\Processor\ProcessorInterface;

abstract class AbstractSelfConfigProvidingProcessor implements ProcessorConfigProviderInterface, ProcessorInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Config\ProcessorConfigInterface>
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

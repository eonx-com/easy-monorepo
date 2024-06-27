<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Factory;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public const DEFAULT_CHANNEL = 'app';

    public function create(?string $channel = null): LoggerInterface;

    public function setHandlerConfigProviders(iterable $handlerConfigProviders): self;

    public function setLoggerConfigurators(iterable $loggerConfigurators): self;

    public function setProcessorConfigProviders(?iterable $processorConfigProviders = null): self;
}

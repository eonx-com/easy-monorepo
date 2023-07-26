<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public const DEFAULT_CHANNEL = 'app';

    public function create(?string $channel = null): LoggerInterface;

    /**
     * @param iterable<mixed> $handlerConfigProviders
     */
    public function setHandlerConfigProviders(iterable $handlerConfigProviders): self;

    /**
     * @param iterable<mixed> $loggerConfigurators
     */
    public function setLoggerConfigurators(iterable $loggerConfigurators): self;

    /**
     * @param null|iterable<mixed> $processorConfigProviders
     */
    public function setProcessorConfigProviders(?iterable $processorConfigProviders = null): self;
}

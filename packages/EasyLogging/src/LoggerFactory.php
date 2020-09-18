<?php

declare(strict_types=1);

namespace EonX\EasyLogging;

use EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface;
use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
use EonX\EasyLogging\Interfaces\Config\LoggerConfiguratorInterface;
use EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigProviderInterface;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

final class LoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var string
     */
    private $defaultChannel;

    /**
     * @var \EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface[]
     */
    private $handlerConfigs = [];

    /**
     * @var string
     */
    private $loggerClass;

    /**
     * @var \EonX\EasyLogging\Interfaces\Config\LoggerConfiguratorInterface[]
     */
    private $loggerConfigurators = [];

    /**
     * @var \Monolog\Logger[]
     */
    private $loggers = [];

    /**
     * @var \EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface[]
     */
    private $processorConfigs = [];

    public function __construct(?string $defaultChannel = null, ?string $loggerClass = null)
    {
        $this->defaultChannel = $defaultChannel ?? self::DEFAULT_CHANNEL;
        $this->loggerClass = $loggerClass ?? Logger::class;
    }

    public function create(?string $channel = null): LoggerInterface
    {
        $channel = $channel ?? $this->defaultChannel;

        if (isset($this->loggers[$channel])) {
            return $this->loggers[$channel];
        }

        $loggerClass = $this->loggerClass;
        $logger = new $loggerClass($channel, $this->getHandlers($channel), $this->getProcessors($channel));

        /** @var \EonX\EasyLogging\Interfaces\Config\LoggerConfiguratorInterface $configurator */
        foreach ($this->getLoggerConfigurators($channel) as $configurator) {
            $configurator->configure($logger);
        }

        return $this->loggers[$channel] = $logger;
    }

    /**
     * @param iterable<mixed> $handlerConfigProviders
     */
    public function setHandlerConfigProviders(iterable $handlerConfigProviders): LoggerFactoryInterface
    {
        foreach ($this->filterIterable($handlerConfigProviders, HandlerConfigProviderInterface::class) as $provider) {
            $configs = $this->filterIterable($provider->handlers(), HandlerConfigInterface::class);

            foreach ($configs as $config) {
                $this->handlerConfigs[] = $config;
            }
        }

        return $this;
    }

    /**
     * @param iterable<mixed> $loggerConfigurators
     */
    public function setLoggerConfigurators(iterable $loggerConfigurators): LoggerFactoryInterface
    {
        $this->loggerConfigurators = $this->filterIterable($loggerConfigurators, LoggerConfiguratorInterface::class);

        return $this;
    }

    /**
     * @param null|iterable<mixed> $processorConfigProviders
     */
    public function setProcessorConfigProviders(?iterable $processorConfigProviders = null): LoggerFactoryInterface
    {
        if ($processorConfigProviders === null) {
            return $this;
        }

        $filtered = $this->filterIterable($processorConfigProviders, ProcessorConfigProviderInterface::class);

        foreach ($filtered as $provider) {
            $configs = $this->filterIterable($provider->processors(), ProcessorConfigInterface::class);

            foreach ($configs as $config) {
                $this->processorConfigs[] = $config;
            }
        }

        return $this;
    }

    /**
     * @param \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[] $configs
     *
     * @return \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[]
     */
    private function filterAndSortConfigs(array $configs, string $channel): array
    {
        $filter = static function ($config) use ($channel): bool {
            // Priority to inclusive channels
            if ($config->getChannels() !== null) {
                return \in_array($channel, $config->getChannels(), true);
            }

            return \in_array($channel, $config->getExceptChannels() ?? [], true) === false;
        };

        return $this->sortConfigs(\array_filter($configs, $filter));
    }

    /**
     * @param iterable<mixed> $iterable
     *
     * @return mixed[]
     */
    private function filterIterable(iterable $iterable, string $class): array
    {
        $iterable = $iterable instanceof \Traversable ? \iterator_to_array($iterable) : (array)$iterable;

        return \array_filter($iterable, static function ($config) use ($class): bool {
            return $config instanceof $class;
        });
    }

    /**
     * @return \Monolog\Handler\HandlerInterface[]
     */
    private function getHandlers(string $channel): array
    {
        $map = static function (HandlerConfigInterface $config): HandlerInterface {
            return $config->handler();
        };

        $handlers = \array_map($map, $this->filterAndSortConfigs($this->handlerConfigs, $channel));

        return \count($handlers) > 0 ? $handlers : [new NullHandler()];
    }

    /**
     * @return \EonX\EasyLogging\Interfaces\Config\LoggerConfiguratorInterface[]|\EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[]
     */
    private function getLoggerConfigurators(string $channel): array
    {
        return $this->filterAndSortConfigs($this->loggerConfigurators, $channel);
    }

    /**
     * @return \Monolog\Processor\ProcessorInterface[]
     */
    private function getProcessors(string $channel): array
    {
        $map = static function (ProcessorConfigInterface $config): ProcessorInterface {
            return $config->processor();
        };

        return \array_map($map, $this->filterAndSortConfigs($this->processorConfigs, $channel));
    }

    /**
     * @param \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[] $configs
     *
     * @return \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[]
     */
    private function sortConfigs(array $configs): array
    {
        \usort(
            $configs,
            static function (LoggingConfigInterface $first, LoggingConfigInterface $second): int {
                return $first->getPriority() <=> $second->getPriority();
            }
        );

        return $configs;
    }
}

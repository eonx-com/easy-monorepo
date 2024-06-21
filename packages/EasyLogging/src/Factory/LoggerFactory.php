<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Factory;

use EonX\EasyLogging\Config\HandlerConfigInterface;
use EonX\EasyLogging\Config\ProcessorConfigInterface;
use EonX\EasyLogging\Configurator\LoggerConfiguratorInterface;
use EonX\EasyLogging\Logger\LazyLogger;
use EonX\EasyLogging\Provider\HandlerConfigProviderInterface;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

final class LoggerFactory implements LazyLoggerFactoryInterface
{
    private string $defaultChannel;

    /**
     * @var \EonX\EasyLogging\Config\HandlerConfigInterface[]
     */
    private array $handlerConfigs = [];

    /**
     * @var array<string, bool>
     */
    private array $initiatedLazyLoggers = [];

    /**
     * @var array<string, bool>
     */
    private array $lazyLoggers = [];

    private string $loggerClass;

    /**
     * @var \EonX\EasyLogging\Configurator\LoggerConfiguratorInterface[]
     */
    private array $loggerConfigurators = [];

    /**
     * @var \Monolog\Logger[]
     */
    private array $loggers = [];

    /**
     * @var \EonX\EasyLogging\Config\ProcessorConfigInterface[]
     */
    private array $processorConfigs = [];

    public function __construct(?string $defaultChannel = null, ?string $loggerClass = null, ?array $lazyLoggers = null)
    {
        $this->defaultChannel = $defaultChannel ?? self::DEFAULT_CHANNEL;
        $this->loggerClass = $loggerClass ?? Logger::class;

        foreach ($lazyLoggers ?? [] as $channel) {
            $this->lazyLoggers[(string)$channel] = true;
        }
    }

    public function create(?string $channel = null): LoggerInterface
    {
        $channel ??= $this->defaultChannel;

        if (isset($this->loggers[$channel])) {
            return $this->loggers[$channel];
        }

        if (isset($this->initiatedLazyLoggers[$channel]) === false && $this->isLazy($channel)) {
            return new LazyLogger($this, $channel);
        }

        $loggerClass = $this->loggerClass;
        /** @var \Monolog\Logger $logger */
        $logger = new $loggerClass($channel, $this->getHandlers($channel), $this->getProcessors($channel));

        /** @var \EonX\EasyLogging\Configurator\LoggerConfiguratorInterface $configurator */
        foreach ($this->getLoggerConfigurators($channel) as $configurator) {
            $configurator->configure($logger);
        }

        return $this->loggers[$channel] = $logger;
    }

    public function initLazyLogger(string $channel): LazyLoggerFactoryInterface
    {
        $this->initiatedLazyLoggers[$channel] = true;

        return $this;
    }

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

    public function setLoggerConfigurators(iterable $loggerConfigurators): LoggerFactoryInterface
    {
        $this->loggerConfigurators = $this->filterIterable($loggerConfigurators, LoggerConfiguratorInterface::class);

        return $this;
    }

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
     * @param \EonX\EasyLogging\Config\LoggingConfigInterface[] $configs
     *
     * @return \EonX\EasyLogging\Config\LoggingConfigInterface[]
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
     * @param class-string $class
     */
    private function filterIterable(iterable $iterable, string $class): array
    {
        return CollectorHelper::filterByClassAsArray($iterable, $class);
    }

    /**
     * @return \Monolog\Handler\HandlerInterface[]
     */
    private function getHandlers(string $channel): array
    {
        /** @var \EonX\EasyLogging\Config\HandlerConfigInterface[] $configs */
        $configs = $this->filterAndSortConfigs($this->handlerConfigs, $channel);

        $handlers = \array_map(
            static fn (HandlerConfigInterface $config): HandlerInterface => $config->handler(),
            $configs
        );

        return \count($handlers) > 0 ? $handlers : [new NullHandler()];
    }

    /**
     * @return \EonX\EasyLogging\Configurator\LoggerConfiguratorInterface[]|\EonX\EasyLogging\Config\LoggingConfigInterface[]
     */
    private function getLoggerConfigurators(string $channel): array
    {
        return $this->filterAndSortConfigs($this->loggerConfigurators, $channel);
    }

    /**
     * @return \Monolog\Processor\ProcessorInterface[]|callable[]
     */
    private function getProcessors(string $channel): array
    {
        /** @var \EonX\EasyLogging\Config\ProcessorConfigInterface[] $configs */
        $configs = $this->filterAndSortConfigs($this->processorConfigs, $channel);

        return \array_map(
            static fn (ProcessorConfigInterface $config): ProcessorInterface => $config->processor(),
            $configs
        );
    }

    private function isLazy(string $channel): bool
    {
        return $this->lazyLoggers['*'] ?? $this->lazyLoggers[$channel] ?? false;
    }

    /**
     * @param \EonX\EasyLogging\Config\LoggingConfigInterface[] $configs
     *
     * @return \EonX\EasyLogging\Config\LoggingConfigInterface[]
     */
    private function sortConfigs(array $configs): array
    {
        return CollectorHelper::orderLowerPriorityFirstAsArray($configs);
    }
}

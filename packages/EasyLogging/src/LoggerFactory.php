<?php

declare(strict_types=1);

namespace EonX\EasyLogging;

use EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface;
use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
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
     * @var \Psr\Log\LoggerInterface[]
     */
    private $loggers = [];

    /**
     * @var \EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface[]
     */
    private $processorConfigs = [];

    /**
     * @param iterable<mixed> $handlerConfigProviders
     * @param null|iterable<mixed> $processorConfigProviders
     */
    public function __construct(
        iterable $handlerConfigProviders,
        ?iterable $processorConfigProviders = null,
        ?string $defaultChannel = null
    ) {
        $this->setHandlerConfigs($handlerConfigProviders);
        $this->setProcessorConfigs($processorConfigProviders);
        $this->defaultChannel = $defaultChannel ?? 'app';
    }

    public function create(?string $channel = null): LoggerInterface
    {
        $channel = $channel ?? $this->defaultChannel;

        if (isset($this->loggers[$channel])) {
            return $this->loggers[$channel];
        }

        $logger = new Logger($channel, $this->getHandlers($channel), $this->getProcessors($channel));

        return $this->loggers[$channel] = $logger;
    }

    /**
     * @param \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[] $configs
     *
     * @return \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[]
     */
    private function filterAndSortConfigs(array $configs, string $channel): array
    {
        $filter = function (LoggingConfigInterface $config) use ($channel): bool {
            return \in_array($channel, $config->channels() ?? [$this->defaultChannel], true);
        };

        return $this->sortConfigs(\array_filter($configs, $filter));
    }

    /**
     * @param iterable<mixed> $configs
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
     * @param iterable<mixed> $providers
     */
    private function setHandlerConfigs(iterable $providers): void
    {
        foreach ($this->filterIterable($providers, HandlerConfigProviderInterface::class) as $provider) {
            $configs = $this->filterIterable($provider->handlers(), HandlerConfigInterface::class);

            foreach ($configs as $config) {
                $this->handlerConfigs[] = $config;
            }
        }
    }

    /**
     * @param iterable<mixed> $providers
     */
    private function setProcessorConfigs(?iterable $providers = null): void
    {
        if ($providers === null) {
            return;
        }

        foreach ($this->filterIterable($providers, ProcessorConfigProviderInterface::class) as $provider) {
            $configs = $this->filterIterable($provider->processors(), ProcessorConfigInterface::class);

            foreach ($configs as $config) {
                $this->processorConfigs[] = $config;
            }
        }
    }

    /**
     * @param \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[] $configs
     *
     * @return \EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface[]
     */
    private function sortConfigs(array $configs): array
    {
        \usort($configs, static function (LoggingConfigInterface $first, LoggingConfigInterface $second): int {
            return $first->priority() <=> $second->priority();
        });

        return $configs;
    }
}

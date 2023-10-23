<?php

declare(strict_types=1);

namespace EonX\EasyLogging;

use EonX\EasyLogging\Interfaces\LazyLoggerFactoryInterface;
use Monolog\ResettableInterface;
use Psr\Log\LoggerInterface;

final class LazyLoggerProxy implements LoggerInterface, ResettableInterface
{
    private ?LoggerInterface $logger = null;

    public function __construct(
        private readonly LazyLoggerFactoryInterface $loggerFactory,
        private readonly string $channel,
    ) {
    }

    public function alert(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->alert($message, $context ?? []);
    }

    public function critical(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->critical($message, $context ?? []);
    }

    public function debug(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->debug($message, $context ?? []);
    }

    public function info(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->info($message, $context ?? []);
    }

    public function emergency(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->emergency($message, $context ?? []);
    }

    public function error(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->error($message, $context ?? []);
    }

    public function log(mixed $level, string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->log($level, $message, $context ?? []);
    }

    public function notice(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->notice($message, $context ?? []);
    }

    public function reset(): void
    {
    }

    public function warning(string|\Stringable $message, ?array $context = null): void
    {
        $this->getLogger()
            ->warning($message, $context ?? []);
    }

    private function getLogger(): LoggerInterface
    {
        return $this->logger ??= $this->loggerFactory->initLazyLogger($this->channel)
            ->create($this->channel);
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Stubs;

use EonX\EasyLogging\Interfaces\LoggerInterface;
use Throwable;

final class LoggerStub implements LoggerInterface
{
    public function alert($message, array $context = []): void
    {
    }

    public function critical($message, array $context = []): void
    {
    }

    public function debug($message, array $context = []): void
    {
    }

    public function emergency($message, array $context = []): void
    {
    }

    public function error($message, array $context = []): void
    {
    }

    public function exception(Throwable $exception, ?string $level = null, ?array $context = null): void
    {
    }

    public function info($message, array $context = []): void
    {
    }

    public function log($level, $message, array $context = []): void
    {
    }

    public function notice($message, array $context = []): void
    {
    }

    public function warning($message, array $context = []): void
    {
    }
}

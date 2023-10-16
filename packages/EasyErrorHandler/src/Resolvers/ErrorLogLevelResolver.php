<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Resolvers;

use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\LogLevelAwareExceptionInterface;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ErrorLogLevelResolver implements ErrorLogLevelResolverInterface
{
    /**
     * @var array<class-string, int>
     */
    private readonly array $exceptionLogLevels;

    /**
     * @param array<class-string, int>|null $exceptionLogLevels
     */
    public function __construct(
        ?array $exceptionLogLevels = null,
    ) {
        $this->exceptionLogLevels = $exceptionLogLevels ?? [HttpExceptionInterface::class => Logger::DEBUG];
    }

    public function getLogLevel(Throwable $throwable): int
    {
        if ($throwable instanceof LogLevelAwareExceptionInterface) {
            return $throwable->getLogLevel();
        }

        foreach ($this->exceptionLogLevels as $class => $logLevel) {
            if (\is_a($throwable, $class)) {
                return $logLevel;
            }
        }

        // Default to error as exceptions not aware of their log level is unexpected
        return Logger::ERROR;
    }
}

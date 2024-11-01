<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Resolver;

use EonX\EasyErrorHandler\Common\Exception\LogLevelAwareExceptionInterface;
use Monolog\Level;
use Throwable;

final readonly class ErrorLogLevelResolver implements ErrorLogLevelResolverInterface
{
    /**
     * @var array<class-string, \Monolog\Level>
     */
    private array $exceptionLogLevels;

    /**
     * @param array<class-string, int>|null $exceptionLogLevels
     */
    public function __construct(
        ?array $exceptionLogLevels = null,
    ) {
        $exceptionLogLevelsAsEnums = [];
        foreach ($exceptionLogLevels ?? [] as $class => $logLevel) {
            $exceptionLogLevelsAsEnums[$class] = Level::from($logLevel);
        }

        $this->exceptionLogLevels = $exceptionLogLevelsAsEnums;
    }

    public function getLogLevel(Throwable $throwable): Level
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
        return Level::Error;
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\State\Provider\DeserializeProvider;
use ApiPlatform\Symfony\EventListener\DeserializeListener;
use Throwable;

abstract class AbstractApiPlatformSerializerExceptionErrorResponseBuilder extends
    AbstractApiPlatformExceptionErrorResponseBuilder
{
    abstract protected function doBuildViolations(Throwable $throwable): array;

    final protected function buildViolations(Throwable $throwable): array
    {
        if ($this->isThrowableFromApiPlatformSerializer($throwable) === false) {
            return [];
        }

        return $this->doBuildViolations($throwable);
    }

    protected function isThrowableFromApiPlatformSerializer(Throwable $throwable): bool
    {
        foreach ($throwable->getTrace() as $trace) {
            if (
                (($trace['class'] ?? '') === DeserializeListener::class && $trace['function'] === 'onKernelRequest')
                || ((($trace['class'] ?? '') === DeserializeProvider::class && $trace['function'] === 'provide'))
            ) {
                return true;
            }
        }

        return false;
    }
}

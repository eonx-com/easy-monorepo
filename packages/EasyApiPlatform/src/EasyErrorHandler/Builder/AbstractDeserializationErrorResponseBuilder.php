<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\State\Provider\DeserializeProvider;
use ApiPlatform\Symfony\EventListener\DeserializeListener;
use Throwable;

abstract class AbstractDeserializationErrorResponseBuilder extends AbstractApiPlatformErrorResponseBuilder
{
    abstract protected function doBuildViolations(Throwable $throwable): array;

    final protected function buildViolations(Throwable $throwable): array
    {
        if ($this->isThrowableFromApiPlatformSerializer($throwable) === false) {
            return [];
        }

        return $this->doBuildViolations($throwable);
    }

    private function isThrowableFromApiPlatformSerializer(Throwable $throwable): bool
    {
        return \array_any(
            $throwable->getTrace(),
            static fn($trace): bool => isset($trace['class'])
                && (
                    ($trace['class'] === DeserializeListener::class && $trace['function'] === 'onKernelRequest')
                    || ($trace['class'] === DeserializeProvider::class && $trace['function'] === 'provide')
                )
        );
    }
}

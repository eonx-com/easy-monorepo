<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use Throwable;

final class ViolationsErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public const DEFAULT_KEY = 'violations';

    protected function doBuildValue(Throwable $throwable, array $data): ?array
    {
        if (($throwable instanceof ValidationExceptionInterface) === false) {
            return null;
        }

        return $throwable->getErrors();
    }

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
    }
}

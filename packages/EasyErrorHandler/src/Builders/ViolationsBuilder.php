<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use Throwable;

final class ViolationsBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    protected function doBuildValue(Throwable $throwable, array $data)
    {
        if (($throwable instanceof ValidationExceptionInterface) === false) {
            return null;
        }

        return $throwable->getErrors();
    }

    protected function getDefaultKey(): string
    {
        return 'violations';
    }
}

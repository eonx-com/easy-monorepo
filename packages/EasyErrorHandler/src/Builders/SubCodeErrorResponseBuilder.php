<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\SubCodeAwareExceptionInterface;
use Throwable;

final class SubCodeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public const DEFAULT_KEY = 'sub_code';

    /**
     * @param mixed[] $data
     */
    protected function doBuildValue(Throwable $throwable, array $data): ?int
    {
        if (($throwable instanceof SubCodeAwareExceptionInterface) === false) {
            return null;
        }

        return $throwable->getSubCode();
    }

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Throwable;

final class CodeBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    /**
     * @param mixed[] $data
     *
     * @return int
     */
    protected function doBuildValue(Throwable $throwable, array $data)
    {
        return $throwable->getCode();
    }

    protected function getDefaultKey(): string
    {
        return 'code';
    }
}

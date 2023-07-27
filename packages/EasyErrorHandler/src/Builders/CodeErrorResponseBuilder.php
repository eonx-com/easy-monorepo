<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Throwable;

final class CodeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public const DEFAULT_KEY = 'code';

    protected function doBuildValue(Throwable $throwable, array $data): int
    {
        return $throwable->getCode();
    }

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
    }
}

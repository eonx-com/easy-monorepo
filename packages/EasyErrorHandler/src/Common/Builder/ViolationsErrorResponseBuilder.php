<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyErrorHandler\Common\Exception\WithErrorListExceptionInterface;
use Throwable;

final class ViolationsErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public const DEFAULT_KEY = 'violations';

    protected function doBuildValue(Throwable $throwable, array $data): ?array
    {
        if (($throwable instanceof WithErrorListExceptionInterface) === false) {
            return null;
        }

        return $throwable->getErrors();
    }

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
    }
}

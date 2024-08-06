<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use EonX\EasyErrorHandler\Common\Exception\SubCodeAwareExceptionInterface;
use Throwable;

final class SubCodeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    protected function doBuildValue(Throwable $throwable, array $data): ?int
    {
        if (($throwable instanceof SubCodeAwareExceptionInterface) === false) {
            return null;
        }

        return $throwable->getSubCode();
    }
}

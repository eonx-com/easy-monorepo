<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\SubCodeAwareExceptionInterface;
use Throwable;

final class SubCodeBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    protected function doBuildValue(Throwable $throwable, array $data)
    {
        if (($throwable instanceof SubCodeAwareExceptionInterface) === false) {
            return null;
        }

        return $throwable->getSubCode();
    }

    protected function getDefaultKey(): string
    {
        return 'sub_code';
    }
}

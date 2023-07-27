<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces\Exceptions;

interface SubCodeAwareExceptionInterface
{
    /**
     * Returns the sub code of an exception.
     */
    public function getSubCode(): int;
}

<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

interface SubCodeAwareExceptionInterface
{
    /**
     * Returns the sub code of an exception.
     */
    public function getSubCode(): int;
}

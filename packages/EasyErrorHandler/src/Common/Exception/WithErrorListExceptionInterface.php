<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

interface WithErrorListExceptionInterface
{
    /**
     * Returns validation errors.
     */
    public function getErrors(): array;
}

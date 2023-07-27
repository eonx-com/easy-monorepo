<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces\Exceptions;

interface ValidationExceptionInterface
{
    /**
     * Returns validation errors.
     */
    public function getErrors(): array;
}

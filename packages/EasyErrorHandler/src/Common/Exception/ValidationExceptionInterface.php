<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

interface ValidationExceptionInterface
{
    /**
     * Returns validation errors.
     */
    public function getErrors(): array;
}

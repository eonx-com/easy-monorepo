<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces\Exceptions;

interface StatusCodeAwareExceptionInterface
{
    /**
     * Returns the HTTP response status code of an exception.
     */
    public function getStatusCode(): int;
}

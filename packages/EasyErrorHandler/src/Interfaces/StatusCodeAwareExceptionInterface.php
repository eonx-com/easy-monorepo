<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface StatusCodeAwareExceptionInterface
{
    /**
     * Returns the HTTP response status code of an exception.
     */
    public function getStatusCode(): int;

    /**
     * Sets the HTTP response status code for an exception.
     */
    public function setStatusCode(int $statusCode);
}

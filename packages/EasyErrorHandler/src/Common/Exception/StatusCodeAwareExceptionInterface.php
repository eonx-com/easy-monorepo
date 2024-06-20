<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

interface StatusCodeAwareExceptionInterface
{
    /**
     * Returns the HTTP response status code of an exception.
     */
    public function getStatusCode(): int;
}

<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

interface StatusCodeAwareExceptionInterface
{
    /**
     * Returns the HTTP response status code of an exception.
     */
    public function getStatusCode(): HttpStatusCode;
}

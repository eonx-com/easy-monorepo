<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

trait StatusCodeAwareExceptionTrait
{
    protected HttpStatusCode $statusCode = HttpStatusCode::InternalServerError;

    public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    /**
     * Sets the HTTP response status code for an exception.
     */
    public function setStatusCode(int|HttpStatusCode $statusCode): self
    {
        $this->statusCode = \is_int($statusCode) ? HttpStatusCode::from($statusCode) : $statusCode;

        return $this;
    }
}

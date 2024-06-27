<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

trait StatusCodeAwareExceptionTrait
{
    protected int $statusCode = 500;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Sets the HTTP response status code for an exception.
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }
}

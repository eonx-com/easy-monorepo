<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

use Symfony\Component\HttpFoundation\Response;

trait StatusCodeAwareExceptionTrait
{
    protected int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

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

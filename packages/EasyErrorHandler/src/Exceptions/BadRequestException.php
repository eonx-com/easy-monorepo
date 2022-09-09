<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use Symfony\Component\HttpFoundation\Response;

abstract class BadRequestException extends BaseException
{
    protected int $statusCode = Response::HTTP_BAD_REQUEST;

    protected ?string $userMessage = 'exceptions.bad_request';
}

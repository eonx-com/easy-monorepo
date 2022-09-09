<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use Symfony\Component\HttpFoundation\Response;

abstract class ForbiddenException extends BaseException
{
    protected int $statusCode = Response::HTTP_FORBIDDEN;

    protected ?string $userMessage = 'exceptions.forbidden';
}

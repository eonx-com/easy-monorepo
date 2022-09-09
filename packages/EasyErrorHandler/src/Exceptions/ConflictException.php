<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use Symfony\Component\HttpFoundation\Response;

abstract class ConflictException extends BaseException
{
    protected int $statusCode = Response::HTTP_CONFLICT;

    protected ?string $userMessage = 'exceptions.conflict';
}

<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class ForbiddenException extends BaseException
{
    protected int $statusCode = 403;

    protected ?string $userMessage = 'exceptions.forbidden';
}

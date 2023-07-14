<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class UnauthorizedException extends BaseException
{
    protected int $statusCode = 401;

    protected string $userMessage = self::USER_MESSAGE_UNAUTHORIZED;
}

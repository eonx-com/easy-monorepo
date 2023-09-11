<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class BadRequestException extends BaseException
{
    protected int $statusCode = 400;

    protected string $userMessage = self::USER_MESSAGE_BAD_REQUEST;
}

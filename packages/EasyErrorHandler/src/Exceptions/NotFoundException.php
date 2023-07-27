<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class NotFoundException extends BaseException
{
    protected int $statusCode = 404;

    protected string $userMessage = self::USER_MESSAGE_NOT_FOUND;
}

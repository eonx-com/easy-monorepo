<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

abstract class ValidationException extends BadRequestException implements WithErrorListExceptionInterface
{
    use ValidationExceptionTrait;

    protected string $userMessage = self::USER_MESSAGE_NOT_VALID;
}

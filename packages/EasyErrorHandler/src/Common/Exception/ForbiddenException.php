<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

abstract class ForbiddenException extends BaseException
{
    protected HttpStatusCode $statusCode = HttpStatusCode::Forbidden;

    protected string $userMessage = self::USER_MESSAGE_FORBIDDEN;
}

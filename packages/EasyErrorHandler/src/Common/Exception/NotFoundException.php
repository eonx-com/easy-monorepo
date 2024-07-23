<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

abstract class NotFoundException extends BaseException
{
    protected HttpStatusCode $statusCode = HttpStatusCode::NotFound;

    protected string $userMessage = self::USER_MESSAGE_NOT_FOUND;
}

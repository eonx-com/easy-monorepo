<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

abstract class ConflictException extends BaseException
{
    protected HttpStatusCode $statusCode = HttpStatusCode::Conflict;

    protected string $userMessage = 'exceptions.conflict';
}

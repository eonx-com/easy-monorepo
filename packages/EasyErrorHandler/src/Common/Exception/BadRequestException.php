<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

abstract class BadRequestException extends BaseException
{
    protected HttpStatusCode $statusCode = HttpStatusCode::BadRequest;

    protected string $userMessage = 'exceptions.bad_request';
}

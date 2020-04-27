<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

class UnauthorizedException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 401;

    /**
     * @var string
     */
    protected $userMessage = 'Unauthorized.';
}

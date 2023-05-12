<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class UnauthorizedException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 401;

    /**
     * @var null|string
     */
    protected $userMessage = 'exceptions.unauthorized';
}

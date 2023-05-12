<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class ForbiddenException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 403;

    /**
     * @var null|string
     */
    protected $userMessage = 'exceptions.forbidden';
}

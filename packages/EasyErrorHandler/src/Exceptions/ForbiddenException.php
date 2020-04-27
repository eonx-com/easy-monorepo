<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

class ForbiddenException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 403;

    /**
     * @var string
     */
    protected $userMessage = 'Forbidden.';
}

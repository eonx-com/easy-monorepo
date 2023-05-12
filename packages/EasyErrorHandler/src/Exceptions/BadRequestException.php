<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class BadRequestException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 400;

    /**
     * @var null|string
     */
    protected $userMessage = 'exceptions.bad_request';
}

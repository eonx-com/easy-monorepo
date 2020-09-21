<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class ConflictException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 409;

    /**
     * @var string
     */
    protected $userMessage = 'exceptions.conflict';
}

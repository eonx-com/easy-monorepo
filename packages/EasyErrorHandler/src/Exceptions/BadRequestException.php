<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

class BadRequestException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 400;

    /**
     * @var string
     */
    protected $userMessage = 'Bad request.';
}

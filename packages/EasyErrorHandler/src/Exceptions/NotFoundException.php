<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class NotFoundException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 404;

    /**
     * @var string
     */
    protected $userMessage = 'exceptions.not_found';
}

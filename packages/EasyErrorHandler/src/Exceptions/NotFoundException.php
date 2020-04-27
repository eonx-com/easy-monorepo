<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

class NotFoundException extends BaseException
{
    /**
     * @var int
     */
    protected $statusCode = 404;

    /**
     * @var string
     */
    protected $userMessage = 'Not found.';
}

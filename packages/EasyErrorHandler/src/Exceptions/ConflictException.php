<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

abstract class ConflictException extends BaseException
{
    protected int $statusCode = 409;

    protected ?string $userMessage = 'exceptions.conflict';
}

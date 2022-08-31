<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use EonX\EasyErrorHandler\Exceptions\Traits\ValidationExceptionTrait;
use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;

abstract class ValidationException extends BadRequestException implements ValidationExceptionInterface
{
    use ValidationExceptionTrait;

    protected ?string $userMessage = 'exceptions.not_valid';
}

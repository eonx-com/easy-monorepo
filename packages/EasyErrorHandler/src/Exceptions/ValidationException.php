<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use EonX\EasyErrorHandler\Exceptions\Traits\ValidationExceptionTrait;
use EonX\EasyErrorHandler\Interfaces\ValidationExceptionInterface;

abstract class ValidationException extends BadRequestException implements ValidationExceptionInterface
{
    use ValidationExceptionTrait;
}

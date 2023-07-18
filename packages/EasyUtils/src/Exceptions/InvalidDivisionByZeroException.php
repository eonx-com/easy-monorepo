<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Exceptions;

use EonX\EasyUtils\Interfaces\EasyUtilsExceptionInterface;
use InvalidArgumentException;

final class InvalidDivisionByZeroException extends InvalidArgumentException implements EasyUtilsExceptionInterface
{
    // No body needed
}

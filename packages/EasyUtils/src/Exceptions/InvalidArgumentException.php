<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Exceptions;

use EonX\EasyUtils\Interfaces\EasyUtilsExceptionInterface;

final class InvalidArgumentException extends \InvalidArgumentException implements EasyUtilsExceptionInterface
{
    // No body needed
}

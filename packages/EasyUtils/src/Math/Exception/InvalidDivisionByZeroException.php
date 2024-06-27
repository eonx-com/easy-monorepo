<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Exception;

use EonX\EasyUtils\Common\Exception\EasyUtilsExceptionInterface;
use InvalidArgumentException;

final class InvalidDivisionByZeroException extends InvalidArgumentException implements EasyUtilsExceptionInterface
{
    // No body needed
}

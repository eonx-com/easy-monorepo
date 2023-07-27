<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Exceptions;

use EonX\EasyUtils\Interfaces\EasyUtilsExceptionInterface;
use RuntimeException;

abstract class AbstractEasyUtilsException extends RuntimeException implements EasyUtilsExceptionInterface
{
}

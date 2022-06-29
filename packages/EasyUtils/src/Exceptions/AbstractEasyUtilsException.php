<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Exceptions;

use EonX\EasyUtils\Interfaces\EasyUtilsExceptionInterface;

abstract class AbstractEasyUtilsException extends \RuntimeException implements EasyUtilsExceptionInterface
{
}

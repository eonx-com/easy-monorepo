<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Exceptions;

use EonX\EasySecurity\Interfaces\EasySecurityExceptionInterface;
use RuntimeException;

abstract class AbstractEasySecurityException extends RuntimeException implements EasySecurityExceptionInterface
{
    // No body needed
}

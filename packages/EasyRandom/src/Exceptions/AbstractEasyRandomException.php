<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Exceptions;

use EonX\EasyRandom\Interfaces\EasyRandomExceptionInterface;
use RuntimeException;

abstract class AbstractEasyRandomException extends RuntimeException implements EasyRandomExceptionInterface
{
    // No body needed
}

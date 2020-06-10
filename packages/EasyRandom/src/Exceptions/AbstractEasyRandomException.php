<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Exceptions;

use EonX\EasyRandom\Interfaces\EasyRandomExceptionInterface;

abstract class AbstractEasyRandomException extends \RuntimeException implements EasyRandomExceptionInterface
{
    // No body needed.
}

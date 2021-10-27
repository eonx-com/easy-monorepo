<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Exceptions;

use EonX\EasyTemplatingBlock\Interfaces\EasyTemplatingBlockExceptionInterface as ExceptionInterface;

abstract class AbstractEasyTemplatingBlockException extends \RuntimeException implements ExceptionInterface
{
    // No body needed.
}

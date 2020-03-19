<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Exceptions;

use EonX\EasyApiToken\Interfaces\EasyApiTokenExceptionInterface;

abstract class AbstractEasyApiTokenException extends \Exception implements EasyApiTokenExceptionInterface
{
    // No body needed.
}

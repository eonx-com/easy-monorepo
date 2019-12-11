<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Exceptions;

use EonX\EasyApiToken\Interfaces\EasyApiTokenExceptionInterface;

abstract class AbstractEasyApiTokenFactoryException extends \Exception implements EasyApiTokenExceptionInterface
{
    // No body needed.
}
